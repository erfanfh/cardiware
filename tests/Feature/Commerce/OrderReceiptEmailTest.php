<?php

namespace Tests\Feature\Commerce;

use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Application\Commerce\DTOs\OrderReceiptData;
use App\Application\Commerce\Jobs\SendOrderReceiptJob;
use App\Application\Commerce\Mails\OrderReceiptMail;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Events\OrderPaidEvent;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Email\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class OrderReceiptEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_transition_queues_one_receipt_without_sending_in_the_payment_request(): void
    {
        Queue::fake();
        Mail::fake();
        $order = $this->order();
        Queue::assertNotPushed(SendOrderReceiptJob::class);

        app(MarkOrderAsPaidAction::class)->execute($order);
        app(MarkOrderAsPaidAction::class)->execute($order);
        event(new OrderPaidEvent($order->id, now()->toImmutable()));

        Queue::assertPushed(SendOrderReceiptJob::class, 1);
        Queue::assertPushed(SendOrderReceiptJob::class, fn ($job) => $job->orderId === $order->id
            && $job->afterCommit === true && $job->connection === 'database');
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_admin_paid_transition_uses_the_same_receipt_listener(): void
    {
        Queue::fake();
        $order = $this->order();

        app(UpdateOrderStatusAction::class)->execute($order, OrderStatusEnum::PAID);

        Queue::assertPushed(SendOrderReceiptJob::class, fn ($job) => $job->orderId === $order->id);
    }

    public function test_sync_default_still_persists_a_real_background_job(): void
    {
        Mail::fake();
        config(['queue.default' => 'sync']);
        $order = $this->order();

        app(MarkOrderAsPaidAction::class)->execute($order);

        $this->assertDatabaseCount('jobs', 1);
        $payload = json_decode(DB::table('jobs')->value('payload'), true);
        $job = unserialize($payload['data']['command']);
        $this->assertInstanceOf(SendOrderReceiptJob::class, $job);
        $this->assertSame($order->id, $job->orderId);
        Mail::assertNothingSent();
    }

    public function test_job_respects_the_configured_worker_connection(): void
    {
        config(['queue.default' => 'redis']);
        $this->assertSame('redis', (new SendOrderReceiptJob(1))->connection);
        config(['mail.order_receipts.connection' => 'database']);
        $this->assertSame('database', (new SendOrderReceiptJob(1))->connection);
    }

    public function test_rollback_discards_receipt_and_commit_queues_it_afterwards(): void
    {
        Queue::fake();
        $order = $this->order();
        DB::beginTransaction();
        app(MarkOrderAsPaidAction::class)->execute($order);
        Queue::assertNotPushed(SendOrderReceiptJob::class);
        DB::rollBack();
        Queue::assertNotPushed(SendOrderReceiptJob::class);
        $this->assertSame(OrderStatusEnum::PENDING_PAYMENT, $order->fresh()->status);

        DB::beginTransaction();
        app(MarkOrderAsPaidAction::class)->execute($order);
        Queue::assertNotPushed(SendOrderReceiptJob::class);
        DB::commit();
        Queue::assertPushed(SendOrderReceiptJob::class, 1);
    }

    #[DataProvider('invalidRecipients')]
    public function test_absent_or_invalid_email_is_skipped_at_dispatch_and_execution(?string $address): void
    {
        Queue::fake();
        Mail::fake();
        $order = $this->order(User::factory()->create(['email' => $address]));
        app(MarkOrderAsPaidAction::class)->execute($order);
        Queue::assertNotPushed(SendOrderReceiptJob::class);

        $this->runJob(new SendOrderReceiptJob($order->id));
        Mail::assertNothingSent();
        $this->assertNull($order->fresh()->receipt_email_sent_at);
    }

    public static function invalidRecipients(): array
    {
        return [[null], [''], [' '], ['invalid-email']];
    }

    public function test_job_rechecks_paid_status_customer_and_current_email(): void
    {
        Queue::fake();
        $transport = Mail::mailer()->getSymfonyTransport();
        $order = $this->order(status: OrderStatusEnum::PAID);
        $order->user->update(['email' => 'updated@example.com']);
        $this->runJob(new SendOrderReceiptJob($order->id));
        $this->assertCount(1, $transport->messages());
        $message = $transport->messages()->first()->getOriginalMessage();
        $this->assertSame('updated@example.com', $message->getTo()[0]->getAddress());
        $this->assertNotEmpty($message->getHtmlBody());
        $this->assertNotEmpty($message->getTextBody());

        Mail::fake();
        $cancelled = $this->order(status: OrderStatusEnum::CANCELLED);
        $refunded = $this->order(status: OrderStatusEnum::REFUNDED);
        $orphan = $this->order(status: OrderStatusEnum::PAID);
        $orphan->user->delete();
        foreach ([$cancelled->id, $refunded->id, $orphan->id, PHP_INT_MAX] as $id) {
            $this->runJob(new SendOrderReceiptJob($id));
        }
        Mail::assertNothingSent();
    }

    public function test_successful_delivery_is_recorded_and_replays_do_not_resend_or_change_updated_at(): void
    {
        $transport = Mail::mailer()->getSymfonyTransport();
        $order = $this->order(status: OrderStatusEnum::PAID);
        $originalUpdatedAt = $order->updated_at;
        $job = unserialize(serialize(new SendOrderReceiptJob($order->id)));
        $this->travel(2)->minutes();

        $this->runJob($job);
        $this->runJob($job);

        $this->assertCount(1, $transport->messages());
        $this->assertNotNull($order->fresh()->receipt_email_sent_at);
        $this->assertNotEmpty($order->fresh()->receipt_email_message_id);
        $this->assertEquals($originalUpdatedAt, $order->fresh()->updated_at);
        Queue::fake();
        event(new OrderPaidEvent($order->id, now()->toImmutable()));
        Queue::assertNotPushed(SendOrderReceiptJob::class);
    }

    public function test_failed_send_is_not_recorded_and_can_be_retried(): void
    {
        $order = $this->order(status: OrderStatusEnum::PAID);
        $this->mock(EmailService::class)->shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP unavailable'));
        $job = new SendOrderReceiptJob($order->id);
        try {
            $this->runJob($job);
            $this->fail('SMTP errors must reach the queue retry mechanism.');
        } catch (RuntimeException $exception) {
            $this->assertSame('SMTP unavailable', $exception->getMessage());
        }
        $this->assertNull($order->fresh()->receipt_email_sent_at);
        $this->app->forgetInstance(EmailService::class);
        $transport = Mail::mailer()->getSymfonyTransport();
        $this->runJob($job);
        $this->assertCount(1, $transport->messages());
        $this->assertNotNull($order->fresh()->receipt_email_sent_at);
    }

    public function test_suppressed_delivery_is_not_recorded_as_sent(): void
    {
        $order = $this->order(status: OrderStatusEnum::PAID);
        $this->mock(EmailService::class)->shouldReceive('send')->once()->andReturnNull();
        try {
            $this->runJob(new SendOrderReceiptJob($order->id));
            $this->fail('A suppressed message must not be recorded as sent.');
        } catch (RuntimeException) {
            $this->assertNull($order->fresh()->receipt_email_sent_at);
        }
    }

    public function test_queue_outage_does_not_fail_the_committed_payment(): void
    {
        $order = $this->order();
        Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Queue unavailable'));

        app(MarkOrderAsPaidAction::class)->execute($order);

        $this->assertTrue($order->fresh()->isPaid());
        $this->assertNull($order->fresh()->receipt_email_sent_at);
    }

    public function test_overlapping_jobs_share_a_lock_and_do_not_send_concurrently(): void
    {
        Mail::fake();
        $order = $this->order(status: OrderStatusEnum::PAID);
        $job = new SendOrderReceiptJob($order->id);
        $middleware = $job->middleware()[0];
        $lock = Cache::lock($middleware->getLockKey($job), 60);
        $this->assertTrue($lock->get());
        $ran = false;
        $middleware->dontRelease()->handle($job, function () use (&$ran): void {
            $ran = true;
        });
        $lock->release();
        $this->assertFalse($ran);
        Mail::assertNothingSent();
    }

    #[DataProvider('paymentGateways')]
    public function test_invoice_uses_purchase_snapshots_discounts_tax_and_only_successful_payment(PaymentGatewayEnum $gateway): void
    {
        $user = User::factory()->create(['display_name' => '<script>buyer</script>']);
        $order = $this->order($user, OrderStatusEnum::PAID);
        $order->update([
            'subtotal_amount' => 220000, 'discount_amount' => 50000, 'coupon_discount_amount' => 30000,
            'tax_rate_basis_points' => 1000, 'tax_amount' => 17000, 'total_amount' => 187000,
            'applied_coupons' => [['code' => 'FIRST'], ['code' => 'SECOND']],
        ]);
        $parent = $order->items()->create([
            'purchasable_type' => ProductSaleItem::class, 'purchasable_id' => 987,
            'quantity' => 2, 'unit_price' => 100000, 'subtotal_amount' => 200000,
            'discount_amount' => 20000, 'total_amount' => 180000,
            'coupon_discount_amount' => 30000, 'final_total_amount' => 150000,
            'snapshot' => ['title' => 'نسخه خریداری‌شده <b>قدیمی</b>'],
        ]);
        $order->items()->create([
            'parent_order_item_id' => $parent->id,
            'purchasable_type' => ProductSaleItem::class, 'purchasable_id' => 988,
            'quantity' => 1, 'unit_price' => 20000, 'subtotal_amount' => 20000,
            'total_amount' => 20000, 'final_total_amount' => 20000,
            'snapshot' => ['title' => 'افزونه خریداری‌شده'],
        ]);
        $order->payments()->create([
            'gateway' => $gateway, 'status' => PaymentStatusEnum::PAID,
            'amount' => 187000, 'tracking_code' => 'SUCCESS-123', 'paid_at' => now(),
            'request_payload' => ['secret' => 'PRIVATE-PAYMENT-DATA'],
        ]);
        $order->payments()->create([
            'gateway' => PaymentGatewayEnum::PAYPING, 'status' => PaymentStatusEnum::FAILED,
            'amount' => 187000, 'tracking_code' => 'FAILED-456',
        ]);
        $receipt = OrderReceiptData::fromOrder($order);
        $this->assertSame(20000, $receipt->productDiscount);
        $this->assertSame(30000, $receipt->couponDiscount);
        $this->assertSame(150000, $receipt->items[0]['total']);
        $this->assertSame(2, $receipt->items[0]['quantity']);
        $this->assertSame($receipt->items[0]['title'], $receipt->items[1]['parent_title']);
        $this->assertSame($gateway->label(), $receipt->paymentMethod);
        $this->assertSame('SUCCESS-123', $receipt->trackingCode);

        $mail = new OrderReceiptMail($receipt);
        $html = $mail->render();
        foreach (['dir="rtl"', $order->order_number, 'FIRST + SECOND', '187,000 تومان', '17,000 تومان', 'SUCCESS-123', route('account.orders'), '&lt;script&gt;buyer&lt;/script&gt;', '&lt;b&gt;قدیمی&lt;/b&gt;'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        foreach (['<script>buyer</script>', 'FAILED-456', 'PRIVATE-PAYMENT-DATA'] as $private) {
            $this->assertStringNotContainsString($private, $html);
        }
        $text = view($mail->content()->text, ['receipt' => $receipt])->render();
        $this->assertStringContainsString('187,000 تومان', $text);
        $this->assertStringContainsString('افزونه خریداری‌شده', $text);
        $this->assertStringContainsString($order->order_number, $mail->envelope()->subject);
    }

    public static function paymentGateways(): array
    {
        return array_map(fn ($gateway) => [$gateway], PaymentGatewayEnum::cases());
    }

    public function test_free_and_manually_confirmed_orders_render_without_a_payment_record(): void
    {
        $order = $this->order(status: OrderStatusEnum::PAID);
        $order->update(['subtotal_amount' => 0, 'total_amount' => 0]);
        $receipt = OrderReceiptData::fromOrder($order);
        $this->assertNull($receipt->trackingCode);
        $this->assertSame(0, $receipt->total);
        $this->assertStringContainsString('0 تومان', (new OrderReceiptMail($receipt))->render());
    }

    private function runJob(SendOrderReceiptJob $job): void
    {
        $job->handle(app(EmailService::class));
    }

    private function order(?User $user = null, OrderStatusEnum $status = OrderStatusEnum::PENDING_PAYMENT): Order
    {
        return Order::query()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'order_number' => 'RECEIPT-'.fake()->unique()->numerify('######'),
            'status' => $status, 'subtotal_amount' => 100000, 'discount_amount' => 0, 'total_amount' => 100000,
            'paid_at' => $status === OrderStatusEnum::PAID ? now() : null,
        ]);
    }
}
