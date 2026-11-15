<?php

namespace Tests\Feature\Marketplace;

use App\Application\Commerce\Actions\Order\SnapshotOrderItemCommissionsAction;
use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\Marketplace\SellerRewards\CloseSellerPeriodAction;
use App\Application\Marketplace\SellerRewards\GetSellerRankingAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\StoreReward;
use App\Domain\Marketplace\Models\StoreSpotlight;
use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\SellerRewardSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * برترین فروشنده‌های هفته/ماه/سال: رتبه با فروش خالص، جایزه‌ی کمیسیون، کد تخفیف و
 * نمایش در صفحه اصلی.
 */
class SellerRewardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        DB::statement('PRAGMA defer_foreign_keys = ON');
    }

    public function test_ranking_counts_paid_marketplace_sales_only(): void
    {
        $lastWeek = LeaderboardPeriod::make('week')->previous();
        $at = $lastWeek->startsAt->copy()->addDay();
        [$big, $bigProduct] = $this->storeWithProduct('Big store', 7000);
        [$small, $smallProduct] = $this->storeWithProduct('Small store', 7000);
        [$site, $siteProduct] = $this->storeWithProduct('Moddingway', 0);

        $this->sale($big, $bigProduct, 3000000, $at);
        $this->sale($small, $smallProduct, 1000000, $at);
        $this->sale($small, $smallProduct, 5000000, $at, OrderStatusEnum::REFUNDED); // مرجوعی حساب نمی‌شود
        $this->sale($site, $siteProduct, 9000000, $at); // محصول خود سایت در رقابت نیست
        $this->sale($big, $bigProduct, 4000000, now()); // این هفته، نه هفته قبل

        $rows = app(GetSellerRankingAction::class)->execute($lastWeek);

        $this->assertSame([$big, $small], $rows->pluck('store_id')->all());
        $this->assertSame([3000000, 1000000], $rows->pluck('net_sales')->all());
    }

    public function test_closing_a_week_rewards_the_winner_with_commission_coupon_and_spotlight(): void
    {
        $lastMonth = LeaderboardPeriod::make('month')->previous();
        $at = $lastMonth->startsAt->copy()->addDays(2);
        $seller = User::factory()->create();
        [$store, $product] = $this->storeWithProduct('Winner store', 7000, $seller);
        [$tiny, $tinyProduct] = $this->storeWithProduct('Tiny store', 7000);
        $this->sale($store, $product, 5000000, $at);
        $this->sale($tiny, $tinyProduct, 100000, $at); // زیر حداقل فروش ماه (۲ میلیون)

        $closure = app(CloseSellerPeriodAction::class)->execute($lastMonth);

        $this->assertSame(1, $closure->winners_count);
        $reward = StoreReward::query()->sole();
        $this->assertSame($store, $reward->store_id);
        $this->assertSame(300, $reward->commission_bonus_bp);
        $this->assertTrue($reward->bonus_ends_at->isFuture());

        $coupon = Coupon::query()->findOrFail($reward->coupon_id);
        $this->assertSame(20, (int) $coupon->value);
        $this->assertSame(1, StoreSpotlight::query()->where('store_id', $store)->where('source', 'reward')->count());
        $this->assertSame(1, DB::table('user_notifications')->where('user_id', $seller->id)->where('event_key', 'seller.reward')->count());

        // فروش تازه‌ی برنده: ۷۰٪ سهم + ۳٪ جایزه.
        $orderId = $this->sale($store, $product, 1000000, now(), OrderStatusEnum::PAID, snapshot: false);
        app(SnapshotOrderItemCommissionsAction::class)->execute(Order::query()->findOrFail($orderId));
        $this->assertSame(7300, (int) DB::table('order_items')->where('order_id', $orderId)->value('seller_commission_basis_points'));

        // بستن دوباره کاری نمی‌کند.
        $this->assertNull(app(CloseSellerPeriodAction::class)->execute($lastMonth));
    }

    public function test_admin_spotlight_shows_the_store_products_on_the_home_section(): void
    {
        [$store] = $this->storeWithProduct('Spotlight store', 7000);
        $admin = $this->staffUser();

        $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\EnsureUserIsAdmin::class)
            ->post(route('admin.seller-rewards.spotlights.store'), ['store_id' => $store, 'days' => 7])
            ->assertRedirect();

        $spotlight = StoreSpotlight::query()->sole();
        $this->assertSame('admin', $spotlight->source);
        $this->assertTrue($spotlight->ends_at->isFuture());

        $this->get(route('admin.seller-rewards.index'))->assertSuccessful()->assertSee('Spotlight store');

        $html = view('front.home.partials.featured-store')->render();
        $this->assertStringContainsString('Spotlight store', $html);
        $this->assertStringContainsString('مشاهده فروشگاه', $html);
        $this->assertStringContainsString('Spotlight store product', $html);
    }

    public function test_admin_can_exclude_and_restore_a_store_in_seller_rankings(): void
    {
        [$store, $product] = $this->storeWithProduct('Excluded store', 7000);
        $period = LeaderboardPeriod::make('week');
        $this->sale($store, $product, 3000000, now());
        $settings = app(SellerRewardSettings::class);
        $data = [
            'enabled' => '1',
            'excluded_store_ids' => [(string) $store, (string) $store],
            'prizes' => collect(SellerRewardSettings::PERIODS)->mapWithKeys(fn ($label, $type) => [$type => $settings->prizes($type)])->all(),
            'min_net_sales' => collect(SellerRewardSettings::PERIODS)->mapWithKeys(fn ($label, $type) => [$type => $settings->minNetSales($type)])->all(),
        ];

        $this->actingAs($this->staffUser())
            ->withoutMiddleware(\App\Http\Middleware\EnsureUserIsAdmin::class)
            ->put(route('admin.seller-rewards.settings'), $data)
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame([$store], $settings->excludedStoreIds());
        $this->assertTrue(app(GetSellerRankingAction::class)->execute($period)->isEmpty());

        unset($data['excluded_store_ids']);
        $this->put(route('admin.seller-rewards.settings'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame([], $settings->excludedStoreIds());
        $this->assertSame([$store], app(GetSellerRankingAction::class)->execute($period)->pluck('store_id')->all());
    }

    /** @return array{0: int, 1: int} */
    private function storeWithProduct(string $title, int $sellerRate, ?User $seller = null): array
    {
        $storeId = $this->insertRow('stores', ['title' => $title, 'slug' => 'store-' . uniqid(), 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $productId = $this->insertRow('products', [
            'store_id' => $storeId, 'title' => $title . ' product', 'slug' => 'p-' . uniqid(), 'status' => 'published',
            'type' => 'digital', 'structure' => 'simple', 'seller_commission_basis_points' => $sellerRate,
            'published_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        if ($seller !== null) {
            DB::table('store_users')->insert(['store_id' => $storeId, 'user_id' => $seller->id, 'created_at' => now(), 'updated_at' => now()]);
        }

        return [$storeId, $productId];
    }

    private function sale(int $storeId, int $productId, int $amount, $paidAt, OrderStatusEnum $status = OrderStatusEnum::PAID, bool $snapshot = true): int
    {
        $buyer = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $buyer->id, 'order_number' => 'S-' . uniqid(), 'status' => $status,
            'subtotal_amount' => $amount, 'discount_amount' => 0, 'total_amount' => $amount, 'paid_at' => $paidAt,
        ]);
        $rate = (int) DB::table('products')->where('id', $productId)->value('seller_commission_basis_points');
        $this->insertRow('order_items', array_merge([
            'order_id' => $order->id, 'product_id' => $productId, 'store_id' => $storeId,
            'purchasable_type' => 'product', 'purchasable_id' => $productId, 'final_total_amount' => $amount,
        ], $snapshot ? ['seller_commission_basis_points' => $rate, 'commission_snapshotted_at' => $paidAt] : []));

        return $order->id;
    }

    private function staffUser(): User
    {
        $roleId = DB::table('roles')->insertGetId(['title' => 'Staff ' . uniqid(), 'is_super_admin' => true, 'created_at' => now(), 'updated_at' => now()]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    private function insertRow(string $table, array $values): int
    {
        foreach (DB::select("PRAGMA table_info('{$table}')") as $column) {
            if ($column->pk || ! $column->notnull || $column->dflt_value !== null || array_key_exists($column->name, $values)) {
                continue;
            }
            $type = strtolower((string) $column->type);
            $values[$column->name] = match (true) {
                str_contains($type, 'int'), str_contains($type, 'numeric'), str_contains($type, 'real') => 0,
                str_contains($type, 'date'), str_contains($type, 'time') => now(),
                default => '',
            };
        }

        return (int) DB::table($table)->insertGetId($values);
    }
}
