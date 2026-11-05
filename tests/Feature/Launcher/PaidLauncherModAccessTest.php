<?php

namespace Tests\Feature\Launcher;

use App\Application\Launcher\Actions\ResolveLauncherModAccessForUserAction;
use App\Application\Launcher\Actions\RevokeLauncherModAccessForOrderAction;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherModGrant;
use App\Domain\Vip\Models\VipSubscription;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * ماد پولی (رایگان / خرید تکی) و دسترسی امانی VIP محصولات لانچری.
 */
class PaidLauncherModAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'paid_mod_test', 'database.connections.paid_mod_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);

        foreach ([new User, new ProductSaleItem, new PurchaseAccess, new Order, new \App\Domain\Commerce\Models\OrderItem, new VipSubscription, new LauncherModGrant] as $model) {
            Schema::create($model->getTable(), function (Blueprint $table) use ($model): void {
                $table->id();
                foreach ($model->getFillable() as $column) {
                    $table->text($column)->nullable();
                }
                if (in_array($model::class, [User::class, ProductSaleItem::class], true)) {
                    $table->softDeletes();
                }
                $table->timestamps();
            });
        }

        DB::table('users')->insert(['id' => 1]);
        DB::table('product_sale_items')->insert([
            'id' => 1, 'product_id' => 1, 'title' => 'Game', 'status' => 'available',
            'launcher_enabled' => true, 'vip_access_enabled' => true,
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('paid_mod_test');
        parent::tearDown();
    }

    private function user(): User
    {
        return User::query()->findOrFail(1);
    }

    private function giveVip(bool $active = true): void
    {
        DB::table('vip_subscriptions')->insert([
            'user_id' => 1,
            'starts_at' => now()->subDays(10),
            'expires_at' => $active ? now()->addDays(20) : now()->subDay(),
        ]);
    }

    /** @return array<string, mixed> */
    private function mod(int $id, string $type): array
    {
        return [
            'id' => $id,
            'is_required' => true,
            'package' => ['files' => [['file_id' => 9]], 'archive_password' => 'secret'],
            'access' => ['type' => $type, 'price_toman' => $type === 'purchase' ? 200000 : null],
        ];
    }

    private function resolve(array $mods): array
    {
        return app(ResolveLauncherModAccessForUserAction::class)->execute($this->user(), $mods);
    }

    public function test_free_mod_stays_open(): void
    {
        [$mod] = $this->resolve([$this->mod(1, 'free')]);

        $this->assertFalse($mod['access']['locked']);
        $this->assertNotEmpty($mod['package']['files']);
    }

    public function test_purchase_mod_is_locked_and_stripped_until_granted(): void
    {
        [$locked] = $this->resolve([$this->mod(1, 'purchase')]);

        $this->assertTrue($locked['access']['locked']);
        $this->assertSame([], $locked['package']['files']);
        $this->assertNull($locked['package']['archive_password']);
        $this->assertFalse($locked['is_required']);
        $this->assertSame(200000, $locked['access']['price_toman']);

        LauncherModGrant::query()->create(['user_id' => 1, 'launcher_mod_id' => 1, 'source' => 'purchase', 'order_id' => 5]);
        [$open] = $this->resolve([$this->mod(1, 'purchase')]);

        $this->assertFalse($open['access']['locked']);
        $this->assertNotEmpty($open['package']['files']);
    }

    public function test_vip_subscription_never_unlocks_a_paid_mod(): void
    {
        // اشتراک ویژه فقط محصول را باز می‌کند؛ ماد پولی فقط با خرید تکی باز می‌شود.
        $this->giveVip();
        [$mod] = $this->resolve([$this->mod(1, 'purchase')]);

        $this->assertTrue($mod['access']['locked']);
        $this->assertSame([], $mod['package']['files']);
        $this->assertSame(0, LauncherModGrant::query()->count());
    }

    public function test_legacy_vip_mod_type_is_served_as_free(): void
    {
        [$mod] = $this->resolve([$this->mod(1, 'vip')]);

        $this->assertFalse($mod['access']['locked']);
        $this->assertSame('free', $mod['access']['type']);
        $this->assertNotEmpty($mod['package']['files']);
    }

    public function test_legacy_vip_permanent_grant_still_opens_a_paid_mod(): void
    {
        LauncherModGrant::query()->create(['user_id' => 1, 'launcher_mod_id' => 1, 'source' => 'vip_permanent']);
        [$mod] = $this->resolve([$this->mod(1, 'purchase')]);

        $this->assertFalse($mod['access']['locked']);
        $this->assertSame('vip_permanent', $mod['access']['unlock_source']);
    }

    public function test_refund_revokes_only_purchase_grants_of_that_order(): void
    {
        LauncherModGrant::query()->create(['user_id' => 1, 'launcher_mod_id' => 1, 'source' => 'purchase', 'order_id' => 5]);
        LauncherModGrant::query()->create(['user_id' => 1, 'launcher_mod_id' => 2, 'source' => 'purchase', 'order_id' => 6]);
        LauncherModGrant::query()->create(['user_id' => 1, 'launcher_mod_id' => 3, 'source' => 'vip_permanent']);

        DB::table('orders')->insert(['id' => 5, 'user_id' => 1]);
        DB::table('order_items')->insert(['order_id' => 5, 'purchasable_type' => 'launcher_mod', 'purchasable_id' => 1]);
        $order = Order::query()->findOrFail(5);
        app(RevokeLauncherModAccessForOrderAction::class)->execute($order);

        $this->assertEqualsCanonicalizing([2, 3], LauncherModGrant::query()->pluck('launcher_mod_id')->map(fn($id) => (int)$id)->all());
    }

    public function test_vip_borrow_access_follows_subscription_and_admin_toggle(): void
    {
        $this->giveVip();
        $borrow = PurchaseAccess::ensureVipBorrowAccess($this->user(), ProductSaleItem::query()->findOrFail(1));

        $this->assertTrue($borrow->isVipBorrow());
        $this->assertTrue($borrow->fresh()->isUsableForLauncher());

        // دسترسی امانی در پرس‌وجوهای «مالکیت» دیده نمی‌شود.
        $this->assertSame(0, PurchaseAccess::query()->withoutGlobalScopes()->usable()->count());
        $this->assertSame(0, PurchaseAccess::query()->withoutGlobalScopes()->owned()->count());
        $this->assertSame(1, PurchaseAccess::query()->withoutGlobalScopes()->launcherUsableFor($this->user())->count());

        // خاموش‌شدن VIP محصول توسط ادمین.
        DB::table('product_sale_items')->update(['vip_access_enabled' => false]);
        $this->assertFalse($borrow->fresh()->isUsableForLauncher());
        DB::table('product_sale_items')->update(['vip_access_enabled' => true]);

        // پایان اشتراک.
        DB::table('vip_subscriptions')->update(['expires_at' => now()->subMinute()]);
        $this->assertFalse($borrow->fresh()->isUsableForLauncher());
        $this->assertSame(0, PurchaseAccess::query()->withoutGlobalScopes()->launcherUsableFor($this->user()->fresh())->count());
    }

    public function test_owned_access_hides_duplicate_borrow_in_library(): void
    {
        $this->giveVip();
        PurchaseAccess::ensureVipBorrowAccess($this->user(), ProductSaleItem::query()->findOrFail(1));
        DB::table('purchase_accesses')->insert([
            'user_id' => 1, 'product_id' => 1, 'product_sale_item_id' => 1, 'status' => 'active',
            'metadata' => json_encode(['source' => 'order_paid']),
        ]);

        $rows = PurchaseAccess::query()->withoutGlobalScopes()->launcherUsableFor($this->user())->get();

        $this->assertCount(1, $rows);
        $this->assertFalse($rows->first()->isVipBorrow());
    }
}
