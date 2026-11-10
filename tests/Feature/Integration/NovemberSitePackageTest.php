<?php

namespace Tests\Feature\Integration;

use App\Application\Commerce\Actions\Access\AdminRestorePurchaseAccessAction;
use App\Application\Commerce\Actions\Access\AdminRevokePurchaseAccessAction;
use App\Application\Commerce\Actions\Cart\AddPurchasableToCartAction;
use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use App\Application\Launcher\Actions\GetLauncherModsAction;
use App\Application\Launcher\Actions\SyncLauncherSaleItemContentAction;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\ClubFollow;
use App\Domain\Vip\Models\VipPlan;
use App\Domain\Vip\Models\VipSubscription;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use App\Http\Resources\Api\V1\Launcher\LauncherCatalogItemResource;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class NovemberSitePackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        parent::tearDown();
    }

    public function test_vip_discount_stacks_after_product_discount_and_reaches_cart_totals(): void
    {
        $user = User::factory()->create();
        $this->giveVip($user, 10);
        $saleItem = $this->saleItem();
        $saleItem->discount()->create(['discounted_price' => 80000]);
        app(UpdateSettingAction::class)->execute('vip', 'purchase_discount_percent', 5);

        $price = app(ResolvePurchasablePriceAction::class)->execute($saleItem, $user);
        $this->assertSame(72000, $price->finalPrice);
        $this->assertSame(28000, $price->unitDiscountAmount);
        $this->assertSame('vip_purchase_discount', $price->discountSourceType);

        $this->actingAs($user);
        app(AddPurchasableToCartAction::class)->execute($saleItem);
        $this->assertDatabaseHas('cart_items', ['subtotal_amount' => 100000, 'unit_price' => 72000, 'discount_amount' => 28000]);
    }

    public function test_global_discount_is_fallback_but_explicit_zero_overrides_it(): void
    {
        app(UpdateSettingAction::class)->execute('vip', 'purchase_discount_percent', 8);
        $user = User::factory()->create();
        $subscription = $this->giveVip($user);
        $saleItem = $this->saleItem();
        $resolve = app(ResolvePurchasablePriceAction::class);
        $this->assertSame(92000, $resolve->execute($saleItem, $user)->finalPrice);

        $subscription->plan->update(['purchase_discount_percent' => 0]);
        $this->assertSame(100000, $resolve->execute($saleItem, $user)->finalPrice);
    }

    public function test_vip_discount_excludes_guests_expired_vip_subscription_items_and_free_items(): void
    {
        app(UpdateSettingAction::class)->execute('vip', 'purchase_discount_percent', 20);
        $user = User::factory()->create();
        $subscription = $this->giveVip($user, 10);
        $saleItem = $this->saleItem();
        $resolve = app(ResolvePurchasablePriceAction::class);

        $this->assertSame(100000, $resolve->execute($saleItem)->finalPrice);
        $saleItem->vip_access_enabled = true;
        $this->assertSame(100000, $resolve->execute($saleItem, $user)->finalPrice);
        $saleItem->vip_access_enabled = false;
        $saleItem->base_price = 0;
        $this->assertSame(0, $resolve->execute($saleItem, $user)->finalPrice);
        $this->assertSame(100000, $resolve->execute($subscription->plan, $user)->finalPrice);

        $subscription->update(['expires_at' => now()->subMinute()]);
        $saleItem->base_price = 100000;
        $this->assertSame(100000, $resolve->execute($saleItem, $user)->finalPrice);
    }

    public function test_pending_bio_is_locked_and_approved_bio_requires_next_month_subscription(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(10));
        $user = User::factory()->create();
        $this->giveVip($user);
        $this->actingAs($user)->put(route('account.loyalty.vip-profile.update'), [
            'club_bio' => 'معرفی اول', 'club_theme' => 'gold',
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->put(route('account.loyalty.vip-profile.update'), [
            'club_bio' => 'معرفی دوم', 'club_theme' => 'night',
        ])->assertSessionHasErrors('club_bio');
        $this->assertSame('معرفی اول', $user->fresh()->club_bio);
        $user->forceFill(['club_bio_status' => 'approved'])->save();
        $this->assertFalse($user->fresh()->canEditClubBio());

        $nextMonth = now()->copy()->startOfMonth()->addMonth();
        $future = $this->giveVip($user, startsAt: $nextMonth);
        $this->assertFalse($user->fresh()->canEditClubBio());
        $this->travelTo($nextMonth->copy()->addHour());
        $this->assertTrue($user->fresh()->canEditClubBio());
        $future->update(['revoked_at' => now()]);
        $this->assertFalse($user->fresh()->canEditClubBio());
    }

    public function test_migration_queues_existing_bios_for_review_without_losing_text(): void
    {
        $migration = require database_path('migrations/2026_11_10_120000_add_vip_features_and_launcher_mods.php');
        $migration->down();
        $user = User::factory()->create();
        $user->forceFill(['club_bio' => 'معرفی موجود پیش از انتشار'])->save();
        $migration->up();

        $this->assertSame('معرفی موجود پیش از انتشار', $user->fresh()->club_bio);
        $this->assertSame('pending', $user->fresh()->club_bio_status);
        $this->assertNotNull($user->fresh()->club_bio_edited_at);
        $this->assertFalse($user->fresh()->canEditClubBio());
    }

    public function test_user_editor_can_moderate_bio_and_regular_users_cannot(): void
    {
        $role = Role::factory()->create();
        $role->permissions()->attach(Permission::query()->firstOrCreate([
            'group' => 'user', 'ability' => 'edit',
        ], ['label' => 'ویرایش کاربر']));
        $editor = User::factory()->create(['role_id' => $role->id]);
        $user = User::factory()->create();
        $user->forceFill(['club_bio' => 'در انتظار بررسی', 'club_bio_status' => 'pending'])->save();
        $this->withoutMiddleware(EnsureUserIsAdmin::class);

        $this->actingAs($user)->post(route('admin.users.club-bio.moderate', $user), ['action' => 'approve'])
            ->assertForbidden();
        $this->actingAs($editor)->post(route('admin.users.club-bio.moderate', $user), ['action' => 'approve'])
            ->assertRedirect(route('admin.users.edit', $user));
        $this->assertSame('approved', $user->fresh()->club_bio_status);
        $this->actingAs($editor)->post(route('admin.users.club-bio.moderate', $user), ['action' => 'reject']);
        $this->assertTrue($user->fresh()->canEditClubBio());
    }

    public function test_follow_toggles_one_relationship_and_rejects_self_inactive_and_guest(): void
    {
        $follower = User::factory()->create();
        $target = User::factory()->create();
        $route = route('loyalty.members.follow', $target);
        $this->post($route)->assertRedirect(route('login'));
        $this->actingAs($follower)->post($route)->assertRedirect(route('loyalty.members.show', $target));
        $this->assertDatabaseCount('club_follows', 1);
        $this->assertSame(1, ClubFollow::query()->where('target_id', $target->id)->count());
        $this->post($route)->assertRedirect();
        $this->assertDatabaseCount('club_follows', 0);
        $this->post(route('loyalty.members.follow', $follower))->assertForbidden();
        $target->update(['status' => 'inactive']);
        $this->post($route)->assertNotFound();
    }

    public function test_revoking_one_access_preserves_other_purchases_and_can_be_restored(): void
    {
        $user = User::factory()->create();
        $admin = $this->admin();
        $access = $this->access($user);
        $other = $this->access($user);

        $this->actingAs($admin)->post(route('admin.launcher-devices.access.revoke', $access), [
            'access_id' => $other->id, 'revoke_reason' => 'مرجوعی جزئی', 'confirm_revoke' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(PurchaseAccessStatusEnum::REVOKED, $access->fresh()->status);
        $this->assertSame(PurchaseAccessStatusEnum::ACTIVE, $other->fresh()->status);
        $this->assertFalse($access->fresh()->isUsableForLauncher());

        app(AdminRestorePurchaseAccessAction::class)->execute($access->fresh(), $admin, 'اصلاح ابطال');
        $this->assertSame(PurchaseAccessStatusEnum::ACTIVE, $access->fresh()->status);
        $this->assertSame($admin->id, $access->fresh()->metadata['restored_by_admin_id']);
    }

    public function test_refunded_orders_cannot_restore_access_and_revoke_requires_permission_and_confirmation(): void
    {
        $user = User::factory()->create();
        $access = $this->access($user);
        $route = route('admin.launcher-devices.access.revoke', $access);
        $this->withoutMiddleware(EnsureUserIsAdmin::class)->actingAs($user)->post($route, [
            'revoke_reason' => 'آزمون دسترسی', 'confirm_revoke' => '1',
        ])->assertForbidden();
        $admin = $this->admin();
        $this->actingAs($admin)->post($route, ['revoke_reason' => 'بدون تأیید'])
            ->assertSessionHasErrors('confirm_revoke');

        app(AdminRevokePurchaseAccessAction::class)->execute($access, $admin, 'مرجوعی محصول');
        $access->order->update(['status' => OrderStatusEnum::REFUNDED]);
        $this->post(route('admin.launcher-devices.access.restore', $access), ['restore_reason' => 'آزمون بازگردانی'])
            ->assertSessionHas('error');
        $this->assertSame(PurchaseAccessStatusEnum::REVOKED, $access->fresh()->status);
    }

    public function test_device_management_page_renders_without_selected_user(): void
    {
        $this->actingAs($this->admin())->get(route('admin.launcher-devices.index'))->assertOk();
    }

    public function test_device_management_lists_selected_user_purchases(): void
    {
        $user = User::factory()->create();
        $access = $this->access($user);
        $this->actingAs($this->admin())->get(route('admin.launcher-devices.index', ['user_id' => $user->id]))
            ->assertOk()
            ->assertSee($access->product->title)
            ->assertSee('محصولات خریداری‌شده و دسترسی‌ها');
    }

    public function test_mod_warning_round_trips_from_storage_to_launcher_payload(): void
    {
        $access = $this->access(User::factory()->create());
        $saleItem = $access->productSaleItem;
        app(SyncLauncherSaleItemContentAction::class)->execute($saleItem, ['launcher_mods' => [[
            'name' => 'Heavy mod', 'slug' => 'heavy-mod', 'version' => '1',
            'install_target' => 'product_root', 'is_enabled' => true,
            'warning_text' => 'ممکن است باعث افت فریم شود', 'warning_level' => 'danger',
        ]]]);
        $mod = $saleItem->launcherMods()->firstOrFail();
        $this->assertSame('danger', $mod->warning_level);
        $payload = app(GetLauncherModsAction::class)->execute($access);
        $this->assertSame(['text' => 'ممکن است باعث افت فریم شود', 'level' => 'danger'], $payload[0]['warning']);
    }

    public function test_game_settings_are_exposed_only_with_usable_access(): void
    {
        $access = $this->access(User::factory()->create());
        $saleItem = $access->productSaleItem;
        $saleItem->product->update(['game_settings_command' => 'Settings.exe']);
        $saleItem->setRelation('purchaseAccesses', collect([$access]));
        $payload = (new LauncherCatalogItemResource($saleItem))->resolve();
        $this->assertSame(['command' => 'Settings.exe'], $payload['game_settings']);

        $access->status = PurchaseAccessStatusEnum::REVOKED;
        $payload = (new LauncherCatalogItemResource($saleItem))->resolve();
        $this->assertArrayNotHasKey('game_settings', $payload);
    }

    public function test_settings_command_rejects_paths_and_non_executables(): void
    {
        $rules = (new UpdateProductRequest)->rules()['game_settings_command'];
        foreach (['Settings.exe', 'Game Settings.EXE', 'tools/settings.exe', 'tools\\Settings.exe', null] as $value) {
            $this->assertTrue(Validator::make(['command' => $value], ['command' => $rules])->passes());
        }
        foreach (['../Settings.exe', '..\\Settings.exe', '/Settings.exe', 'C:\\Settings.exe', 'Settings.txt', "bad\nSettings.exe"] as $value) {
            $this->assertTrue(Validator::make(['command' => $value], ['command' => $rules])->fails(), (string) $value);
        }
    }

    private function admin(): User
    {
        return User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
    }

    private function giveVip(User $user, ?int $percent = null, $startsAt = null): VipSubscription
    {
        $plan = VipPlan::query()->create([
            'code' => 'vip-'.uniqid(), 'title' => 'پلن ویژه', 'duration_days' => 30,
            'price_toman' => 100000, 'purchase_discount_percent' => $percent, 'is_active' => true,
        ]);
        $order = $this->paidOrder($user);
        $item = $order->items()->create([
            'purchasable_type' => $plan->getMorphClass(), 'purchasable_id' => $plan->id,
            'quantity' => 1, 'unit_price' => 100000,
        ]);

        return VipSubscription::query()->create([
            'user_id' => $user->id, 'vip_plan_id' => $plan->id, 'order_id' => $order->id,
            'order_item_id' => $item->id, 'duration_days' => 30,
            'starts_at' => $startsAt ?? now()->subDays(5),
            'expires_at' => ($startsAt ?? now())->copy()->addDays(30),
        ]);
    }

    private function saleItem(): ProductSaleItem
    {
        $storeId = DB::table('stores')->insertGetId([
            'title' => 'Test Store', 'slug' => 'store-'.uniqid(), 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $productId = DB::table('products')->insertGetId([
            'store_id' => $storeId, 'title' => 'Test Product', 'slug' => 'product-'.uniqid(),
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active',
            'published_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return ProductSaleItem::query()->create([
            'product_id' => $productId, 'title' => 'Test Edition', 'status' => 'available',
            'base_price' => 100000, 'launcher_enabled' => true, 'version' => '1',
        ]);
    }

    private function access(User $user): PurchaseAccess
    {
        $saleItem = $this->saleItem();
        $order = $this->paidOrder($user);
        $item = $order->items()->create([
            'purchasable_type' => $saleItem->getMorphClass(), 'purchasable_id' => $saleItem->id,
            'quantity' => 1, 'unit_price' => 100000,
        ]);

        return PurchaseAccess::query()->create([
            'user_id' => $user->id, 'order_id' => $order->id, 'order_item_id' => $item->id,
            'product_id' => $saleItem->product_id, 'product_sale_item_id' => $saleItem->id,
            'status' => PurchaseAccessStatusEnum::ACTIVE,
        ]);
    }

    private function paidOrder(User $user): Order
    {
        return Order::query()->create([
            'user_id' => $user->id, 'order_number' => 'TEST-'.uniqid(), 'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 100000, 'total_amount' => 100000, 'paid_at' => now(),
        ]);

    }
}
