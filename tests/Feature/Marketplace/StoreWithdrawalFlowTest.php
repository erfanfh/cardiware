<?php

namespace Tests\Feature\Marketplace;

use App\Application\Marketplace\Actions\GetStoreWithdrawalBalanceAction;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Marketplace\Enums\StoreWithdrawalStatusEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreWithdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreWithdrawalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_requests_reserve_store_balance_and_approval_deducts_it_once(): void
    {
        [$store, $seller, $product] = $this->setupStore();
        $secondSeller = User::factory()->create();
        $store->sellers()->attach($secondSeller);
        $this->order($product, OrderStatusEnum::PAID, 500000, 300000);
        $this->order($product, OrderStatusEnum::REFUNDED, 900000, 900000);

        $key = (string) Str::uuid();
        $this->actingAs($seller)->get(route('seller.stores.withdrawals', $store))
            ->assertOk()
            ->assertViewHas('balance', fn (array $balance) => $balance['earned'] === 300000);
        $this->post(route('seller.stores.withdrawals.store', $store), $this->payload(200000, $key))
            ->assertRedirect(route('seller.stores.withdrawals', $store));
        $this->post(route('seller.stores.withdrawals.store', $store), $this->payload(200000, $key))->assertRedirect();
        $this->assertDatabaseCount('store_withdrawals', 1);

        $withdrawal = StoreWithdrawal::query()->firstOrFail();
        $this->assertSame(StoreWithdrawalStatusEnum::PENDING, $withdrawal->status);
        $this->assertSame(100000, $this->balance($store)['available']);
        $this->assertSame(0, $this->balance($store)['approved']);
        $this->assertNotSame($withdrawal->iban, DB::table('store_withdrawals')->value('iban'));
        $this->assertNotSame($withdrawal->card_number, DB::table('store_withdrawals')->value('card_number'));
        $this->actingAs($secondSeller)->post(route('seller.stores.withdrawals.store', $store), $this->payload(150000))
            ->assertSessionHasErrors('amount');
        $this->get(route('seller.stores.withdrawals', $store))->assertOk()->assertSee($withdrawal->maskedIban());

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.store-withdrawals.index'))->assertOk()->assertSee($store->title);
        $this->get(route('admin.store-withdrawals.show', $withdrawal))->assertOk()->assertSee($withdrawal->iban);
        $this->post(route('admin.store-withdrawals.approve', $withdrawal), [
            'payment_reference' => 'BANK-123', 'admin_note' => 'توضیح ثبت‌شده توسط ادمین',
        ])
            ->assertRedirect();
        $this->assertSame(StoreWithdrawalStatusEnum::APPROVED, $withdrawal->fresh()->status);
        $this->assertSame('BANK-123', $withdrawal->fresh()->payment_reference);
        $this->assertSame(200000, $this->balance($store)['approved']);
        $this->assertSame(100000, $this->balance($store)['available']);
        $this->actingAs($seller)->get(route('seller.stores.withdrawals', $store))
            ->assertOk()->assertSee('BANK-123')->assertSee('توضیح ثبت‌شده توسط ادمین');
        $this->actingAs($admin);
        $this->post(route('admin.store-withdrawals.approve', $withdrawal))->assertSessionHas('error');
        $this->assertSame(200000, $this->balance($store)['approved']);
        $this->actingAs($seller)->post(route('seller.stores.withdrawals.store', $store), $this->payload(200000, $key))
            ->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('store_withdrawals', 1);
    }

    public function test_rejection_releases_reservation_and_refunds_can_block_approval(): void
    {
        [$store, $seller, $product] = $this->setupStore();
        $paid = $this->order($product, OrderStatusEnum::PAID, 200000, 150000);
        $this->actingAs($seller)->post(route('seller.stores.withdrawals.store', $store), $this->payload(100000));
        $withdrawal = StoreWithdrawal::query()->firstOrFail();
        $paid->update(['status' => OrderStatusEnum::REFUNDED]);

        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.store-withdrawals.approve', $withdrawal))
            ->assertSessionHas('error');
        $this->assertSame(StoreWithdrawalStatusEnum::PENDING, $withdrawal->fresh()->status);
        $this->post(route('admin.store-withdrawals.reject', $withdrawal), ['reason' => 'اطلاعات ناقص است.'])->assertRedirect();
        $this->assertSame(StoreWithdrawalStatusEnum::REJECTED, $withdrawal->fresh()->status);
        $this->assertSame(0, $this->balance($store)['pending']);
        $this->actingAs($seller)->get(route('seller.stores.withdrawals', $store))
            ->assertOk()->assertSee('دلیل رد:')->assertSee('اطلاعات ناقص است.');
        $this->actingAs($admin);
        $this->post(route('admin.store-withdrawals.reject', $withdrawal), ['reason' => 'تکراری'])->assertSessionHas('error');

        $this->order($product, OrderStatusEnum::PAID, 200000, 150000);
        $this->assertSame(150000, $this->balance($store)['available']);
    }

    public function test_limits_validation_and_store_access_are_enforced(): void
    {
        [$store, $seller, $product] = $this->setupStore();
        $this->order($product, OrderStatusEnum::PAID, 500000, 300000);
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('seller.stores.withdrawals', $store))->assertForbidden();
        $this->post(route('seller.stores.withdrawals.store', $store), $this->payload(100000))->assertForbidden();
        $this->get(route('admin.store-withdrawals.index'))->assertRedirect(route('account.dashboard'));

        $this->actingAs($this->admin())->put(route('admin.store-withdrawals.settings'), [
            'minimum_amount' => 250000, 'maximum_amount' => 200000,
        ])->assertSessionHasErrors('maximum_amount');
        $this->put(route('admin.store-withdrawals.settings'), [
            'minimum_amount' => 100000, 'maximum_amount' => 200000,
        ])->assertRedirect();

        $this->actingAs($seller)->post(route('seller.stores.withdrawals.store', $store), $this->payload(90000))
            ->assertSessionHasErrors('amount');
        $this->post(route('seller.stores.withdrawals.store', $store), $this->payload(250000))
            ->assertSessionHasErrors('amount');
        $this->post(route('seller.stores.withdrawals.store', $store), [
            ...$this->payload(150000), 'iban' => 'IR123',
        ])->assertSessionHasErrors('iban');
        $this->post(route('seller.stores.withdrawals.store', $store), [
            ...$this->payload(150000),
            'amount' => '۱۵۰٬۰۰۰',
            'iban' => substr($this->iban(), 2),
            'card_number' => '6037 9912 3456 7890',
        ])->assertRedirect();
        $this->assertDatabaseCount('store_withdrawals', 1);
        $this->assertSame(150000, StoreWithdrawal::query()->firstOrFail()->amount);
    }

    private function setupStore(): array
    {
        $seller = User::factory()->create();
        $store = Store::query()->create(['title' => 'فروشگاه تست برداشت', 'slug' => 'withdrawal-test', 'status' => StoreStatusEnum::ACTIVE]);
        $store->sellers()->attach($seller);
        $product = Product::query()->create([
            'store_id' => $store->id, 'title' => 'محصول تست', 'slug' => 'withdrawal-product',
            'type' => ProductTypeEnum::DIGITAL, 'structure' => ProductStructureEnum::SIMPLE,
            'status' => ProductStatusEnum::ACTIVE, 'published_at' => now(),
        ]);
        return [$store, $seller, $product];
    }

    private function order(Product $product, OrderStatusEnum $status, int $total, int $sellerAmount): Order
    {
        $order = Order::query()->create([
            'user_id' => User::factory()->create()->id, 'order_number' => 'SW-'.uniqid(),
            'status' => $status, 'subtotal_amount' => $total, 'total_amount' => $total,
            'paid_at' => now(),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id, 'purchasable_type' => 'product_sale_item', 'purchasable_id' => 1,
            'product_id' => $product->id, 'store_id' => $product->store_id, 'quantity' => 1,
            'unit_price' => $total, 'subtotal_amount' => $total, 'total_amount' => $total,
            'final_total_amount' => $total, 'seller_commission_amount' => $sellerAmount,
            'platform_commission_amount' => $total - $sellerAmount,
            'seller_commission_basis_points' => (int) (10000 * $sellerAmount / $total),
            'platform_commission_basis_points' => 10000 - (int) (10000 * $sellerAmount / $total),
        ]);
        return $order;
    }

    private function payload(int $amount, ?string $key = null): array
    {
        return [
            'amount' => $amount, 'iban' => $this->iban(), 'card_number' => '6037991234567890',
            'account_holder' => 'فروشندهٔ آزمایشی', 'request_key' => $key ?? (string) Str::uuid(),
        ];
    }

    private function iban(): string
    {
        $bban = '0123456789012345678901';
        for ($check = 0; $check < 100; $check++) {
            $candidate = str_pad((string) $check, 2, '0', STR_PAD_LEFT).$bban;
            $remainder = 0;
            foreach (str_split($candidate.'1827') as $digit) {
                $remainder = ($remainder * 10 + (int) $digit) % 97;
            }
            if ($remainder === 1) {
                return 'IR'.$candidate;
            }
        }
        throw new \LogicException('Unable to build an IBAN.');
    }

    private function balance(Store $store): array
    {
        return app(GetStoreWithdrawalBalanceAction::class)->execute($store);
    }

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
