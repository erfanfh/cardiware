<?php

namespace Tests\Feature\Notification;

use App\Application\Commerce\Actions\Order\MarkOrderAsPaidAction;
use App\Application\Notification\Jobs\SendCustomerSmsJob;
use App\Application\Notification\Support\CustomerSmsPayloadFactory;
use App\Application\Support\Actions\CreateTicketMessageAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Notification\Enums\CustomerSmsType;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketMessage;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Setting\Groups\NotificationsSettings;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CustomerSmsNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
    }

    public function test_both_notifications_are_disabled_by_default(): void
    {
        Queue::fake();
        $this->assertFalse($this->settings()->get('ticket_reply_sms_enabled', false));
        $this->assertFalse($this->settings()->get('order_paid_sms_enabled', false));
        $ticket = $this->ticket();
        $this->reply($ticket);
        app(MarkOrderAsPaidAction::class)->execute($this->order());

        Queue::assertNotPushed(SendCustomerSmsJob::class);
    }

    public function test_only_support_replies_notify_the_ticket_owner_and_each_reply_has_its_own_job(): void
    {
        Queue::fake();
        $this->settings()->set('ticket_reply_sms_enabled', true);
        $ticket = $this->ticket();

        app(CreateTicketMessageAction::class)->execute($ticket, $ticket->user, TicketMessageSenderTypeEnum::USER, 'Customer message', false);
        app(CreateTicketMessageAction::class)->execute($ticket, null, TicketMessageSenderTypeEnum::SYSTEM, 'System message');
        Queue::assertNotPushed(SendCustomerSmsJob::class);

        $first = $this->reply($ticket);
        $second = $this->reply($ticket);
        Queue::assertPushed(SendCustomerSmsJob::class, 2);
        foreach ([$first, $second] as $message) {
            Queue::assertPushed(SendCustomerSmsJob::class, fn ($job) => $job->type === CustomerSmsType::TICKET_REPLY
                && $job->subjectId === $message->id && $job->afterCommit === true);
        }
    }

    public function test_purchase_alert_is_dispatched_on_payment_once_and_has_an_independent_switch(): void
    {
        Queue::fake();
        $this->settings()->set('order_paid_sms_enabled', true);
        $order = $this->order();
        $this->reply($this->ticket());
        Queue::assertNotPushed(SendCustomerSmsJob::class);

        app(MarkOrderAsPaidAction::class)->execute($order);
        app(MarkOrderAsPaidAction::class)->execute($order);
        Queue::assertPushed(SendCustomerSmsJob::class, 1);
        Queue::assertPushed(SendCustomerSmsJob::class, fn ($job) => $job->type === CustomerSmsType::ORDER_PAID && $job->subjectId === $order->id);
    }

    #[DataProvider('notificationTypes')]
    public function test_rollback_discards_notification_dispatch(CustomerSmsType $type): void
    {
        Queue::fake();
        $this->settings()->set($type->settingKey(), true);
        $ticket = $this->ticket();
        $order = $this->order();
        DB::beginTransaction();
        if ($type === CustomerSmsType::TICKET_REPLY) {
            $this->reply($ticket);
        } else {
            app(MarkOrderAsPaidAction::class)->execute($order);
        }
        Queue::assertNotPushed(SendCustomerSmsJob::class);
        DB::rollBack();
        Queue::assertNotPushed(SendCustomerSmsJob::class);
        $this->assertSame(OrderStatusEnum::PENDING_PAYMENT, $order->fresh()->status);
        $this->assertSame(0, $ticket->messages()->count());
    }

    #[DataProvider('notificationTypes')]
    public function test_job_sends_to_customer_and_records_delivery_to_prevent_replays(CustomerSmsType $type): void
    {
        Queue::fake();
        $this->configureSms($type);
        $customer = User::factory()->create(['mobile' => '09123456789', 'display_name' => 'خریدار']);
        $subject = $type === CustomerSmsType::TICKET_REPLY ? $this->reply($this->ticket($customer)) : $this->order($customer, OrderStatusEnum::PAID);
        $number = $type === CustomerSmsType::TICKET_REPLY ? (string) $subject->ticket->number : $subject->order_number;
        $numberParameter = $type === CustomerSmsType::TICKET_REPLY ? 'TicketNumber' : 'OrderNumber';
        $originalUpdatedAt = $subject->updated_at;
        $this->mock(SmsService::class)->shouldReceive('sendPattern')->once()
            ->with('09123456789', '1234', ['Name' => 'خریدار', $numberParameter => $number])->andReturn('tracking-123');

        $job = unserialize(serialize(new SendCustomerSmsJob($type, $subject->id)));
        $this->runJob($job);
        $this->runJob($job);

        $this->assertNotNull($subject->fresh()->customer_sms_sent_at);
        $this->assertSame('tracking-123', $subject->fresh()->customer_sms_message_id);
        $this->assertEquals($originalUpdatedAt, $subject->fresh()->updated_at);
    }

    #[DataProvider('notificationTypes')]
    public function test_disabling_a_notification_also_stops_jobs_waiting_in_the_queue(CustomerSmsType $type): void
    {
        Queue::fake();
        $this->settings()->set($type->settingKey(), true);
        $subject = $type === CustomerSmsType::TICKET_REPLY ? $this->reply($this->ticket()) : $this->order(status: OrderStatusEnum::PAID);
        $this->settings()->set($type->settingKey(), false);
        $this->app->bind(SmsService::class, fn () => throw new RuntimeException('SMS must not be resolved'));

        $this->runJob(new SendCustomerSmsJob($type, $subject->id));
        $this->assertNull($subject->fresh()->customer_sms_sent_at);
    }

    public function test_sms_failure_can_be_retried_without_marking_the_order_as_sent_early(): void
    {
        Queue::fake();
        $this->configureSms(CustomerSmsType::ORDER_PAID);
        $order = $this->order(status: OrderStatusEnum::PAID);
        $attempts = 0;
        $this->mock(SmsService::class)->shouldReceive('sendPattern')->twice()->andReturnUsing(function () use (&$attempts): string {
            if (++$attempts === 1) {
                throw new RuntimeException('Provider unavailable');
            }

            return 'retry-success';
        });

        $job = new SendCustomerSmsJob(CustomerSmsType::ORDER_PAID, $order->id);
        try {
            $this->runJob($job);
            $this->fail('The provider failure must reach the queue retry mechanism.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Provider unavailable', $exception->getMessage());
        }
        $this->assertNull($order->fresh()->customer_sms_sent_at);
        $this->runJob($job);
        $this->assertSame('retry-success', $order->fresh()->customer_sms_message_id);
    }

    public function test_missing_mobile_and_cancelled_orders_are_skipped_before_resolving_sms(): void
    {
        Queue::fake();
        $this->settings()->set('order_paid_sms_enabled', true);
        $this->app->bind(SmsService::class, fn () => throw new RuntimeException('SMS must not be resolved'));
        $user = User::factory()->create(['mobile' => '']);
        $order = $this->order($user, OrderStatusEnum::PAID);
        $cancelled = $this->order(status: OrderStatusEnum::CANCELLED);
        foreach ([$order, $cancelled] as $subject) {
            $this->runJob(new SendCustomerSmsJob(CustomerSmsType::ORDER_PAID, $subject->id));
            $this->assertNull($subject->fresh()->customer_sms_sent_at);
        }
    }

    public function test_dispatch_outage_does_not_undo_the_support_reply(): void
    {
        $this->settings()->set('ticket_reply_sms_enabled', true);
        Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Queue unavailable'));
        $ticket = $this->ticket();
        $message = $this->reply($ticket);

        $this->assertTrue($message->exists);
        $this->assertDatabaseHas('ticket_messages', ['id' => $message->id, 'body' => 'Support reply']);
        $this->assertSame('answered', $ticket->fresh()->status->value);
    }

    public function test_admin_can_render_save_and_disable_sms_preferences_without_changing_in_app_preferences(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin)->get(route('admin.settings.notifications'))
            ->assertOk()->assertSee('ticket_reply_sms_enabled')->assertSee('order_paid_sms_enabled')->assertSee('اعلان‌های داخل سیستم');

        $url = route('admin.settings.notifications.sms.update');
        $this->put($url, ['ticket_reply_sms_enabled' => '1', 'order_paid_sms_enabled' => '0', 'event_key' => 'comment.replied'])
            ->assertRedirect(route('admin.settings.notifications'))->assertSessionHasNoErrors();
        $this->assertTrue($this->settings()->get('ticket_reply_sms_enabled'));
        $this->assertFalse($this->settings()->get('order_paid_sms_enabled'));
        $this->assertDatabaseCount('notification_event_settings', 0);

        $this->put($url, ['ticket_reply_sms_enabled' => '0', 'order_paid_sms_enabled' => '1'])->assertSessionHasNoErrors();
        $this->assertFalse($this->settings()->get('ticket_reply_sms_enabled'));
        $this->assertTrue($this->settings()->get('order_paid_sms_enabled'));
        $this->put($url, ['ticket_reply_sms_enabled' => 'yes'])->assertSessionHasErrors(['ticket_reply_sms_enabled', 'order_paid_sms_enabled']);

        $this->get(route('admin.settings.general', ['tab' => 'sms-panel']))->assertOk()
            ->assertSee('smsir_ticket_reply_pattern')->assertSee('farazsms_order_paid_pattern')->assertSee('melipayamak_order_paid_pattern');
    }

    public function test_settings_require_permission_and_validate_pattern_parameter_names(): void
    {
        $user = User::factory()->create();
        Gate::define('admin.display', fn () => true);
        Gate::define('setting.general', fn () => false);
        $this->actingAs($user)->get(route('admin.settings.notifications'))->assertForbidden();
        $this->put(route('admin.settings.notifications.sms.update'), ['ticket_reply_sms_enabled' => 1, 'order_paid_sms_enabled' => 1])->assertForbidden();
        Gate::define('setting.general', fn () => true);
        $this->put(route('admin.settings.general.store'), [
            'smsir_order_paid_pattern' => '1234', 'smsir_order_paid_name_parameter' => 'OrderNumber',
        ])->assertSessionHasErrors('smsir_order_paid_order_number_parameter');
        $this->assertFalse($this->settings()->get('order_paid_sms_enabled', false));
    }

    public static function notificationTypes(): array
    {
        return [[CustomerSmsType::TICKET_REPLY], [CustomerSmsType::ORDER_PAID]];
    }

    private function settings(): NotificationsSettings
    {
        return app(NotificationsSettings::class);
    }

    private function configureSms(CustomerSmsType $type): void
    {
        $this->settings()->set($type->settingKey(), true);
        $general = app(GeneralSettings::class);
        $general->set('default_sms_provider', SmsIrProvider::class);
        $general->set('smsir_'.$type->value.'_pattern', '1234');
    }

    private function runJob(SendCustomerSmsJob $job): void
    {
        $job->handle($this->settings(), app(CustomerSmsPayloadFactory::class));
    }

    private function order(?User $user = null, OrderStatusEnum $status = OrderStatusEnum::PENDING_PAYMENT): Order
    {
        return Order::query()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'order_number' => 'SMS-'.fake()->unique()->numerify('######'),
            'status' => $status, 'subtotal_amount' => 100000, 'discount_amount' => 0, 'total_amount' => 100000,
        ]);
    }

    private function ticket(?User $user = null): Ticket
    {
        return Ticket::query()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'number' => fake()->unique()->numberBetween(1000, 999999), 'title' => 'SMS test ticket', 'status' => 'open',
        ]);
    }

    private function reply(Ticket $ticket): TicketMessage
    {
        return app(CreateTicketMessageAction::class)->execute(
            $ticket, User::factory()->create(), TicketMessageSenderTypeEnum::SUPPORT, 'Support reply',
        );
    }
}
