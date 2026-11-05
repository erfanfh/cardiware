<?php

namespace Tests\Feature\Loyalty;

use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\Champions\CloseChampionPeriodAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\Support\ClubEliteMembership;
use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\LoyaltyChampion;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Domain\Loyalty\Models\LoyaltyPeriodClosure;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Promotion\Models\Coupon;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * فاز ۲ باشگاه: برترین‌های ماه/فصل/سال، حد نصاب کاربر برتر سال، کد قرعه‌کشی خرید،
 * کانون نخبگان و صفحه‌های تالار افتخار و پروفایل عضو.
 */
class LoyaltyChampionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        parent::tearDown();
    }

    public function test_last_month_top_three_get_points_coupon_and_a_notification_once(): void
    {
        $lastMonth = LeaderboardPeriod::make('month')->previous();
        $inLastMonth = $lastMonth->startsAt->copy()->addDays(3);

        $users = collect([900, 700, 500, 300])->map(function (int $xp) use ($inLastMonth): User {
            $user = User::factory()->create();
            $this->earn($user, $xp, $inLastMonth);

            return $user;
        });
        $staff = $this->staffUser();
        $this->earn($staff, 5000, $inLastMonth);

        $closure = app(CloseChampionPeriodAction::class)->execute($lastMonth);

        $this->assertSame(3, $closure->winners_count);
        $champions = LoyaltyChampion::query()->orderBy('rank')->get();
        $this->assertSame($users->take(3)->pluck('id')->all(), $champions->pluck('user_id')->all());
        $this->assertSame([900, 700, 500], $champions->pluck('score')->all());

        // نفر اول: ۱۰۰۰ امتیاز جایزه (بدون XP) + کد تخفیف ۲۰٪ شخصی
        $first = $champions->first();
        $account = PointAccount::query()->where('user_id', $first->user_id)->first();
        $this->assertSame(900 + 1000, (int) $account->balance);
        $this->assertSame(900, (int) $account->lifetime_xp);
        $coupon = Coupon::query()->findOrFail($first->coupon_id);
        $this->assertSame(20, (int) $coupon->value);
        $this->assertSame(1, DB::table('user_notifications')->where('user_id', $first->user_id)->where('event_key', 'loyalty.champion')->count());

        // بستن دوباره همان دوره کاری نمی‌کند.
        $this->assertNull(app(CloseChampionPeriodAction::class)->execute($lastMonth));
        $this->assertSame(3, LoyaltyChampion::query()->count());
    }

    public function test_a_period_that_is_still_running_is_not_closed(): void
    {
        $this->assertNull(app(CloseChampionPeriodAction::class)->execute(LeaderboardPeriod::make('month')));
        $this->assertSame(0, LoyaltyPeriodClosure::query()->count());
    }

    public function test_user_of_the_year_must_meet_the_yearly_thresholds(): void
    {
        app(UpdateSettingAction::class)->execute('loyalty', 'champion_year_min_xp', 1000);
        app(UpdateSettingAction::class)->execute('loyalty', 'champion_year_min_missions', 0);
        app(UpdateSettingAction::class)->execute('loyalty', 'champion_year_min_orders', 1);
        app(UpdateSettingAction::class)->execute('loyalty', 'champion_year_grand_prize', 'PS5');

        $lastYear = LeaderboardPeriod::make('year')->previous();
        $inLastYear = $lastYear->startsAt->copy()->addMonths(2);

        $noPurchases = User::factory()->create();
        $this->earn($noPurchases, 9000, $inLastYear);
        $buyer = User::factory()->create();
        $this->earn($buyer, 2000, $inLastYear);
        Order::query()->create([
            'user_id' => $buyer->id, 'order_number' => 'Y-' . uniqid(), 'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 100000, 'discount_amount' => 0, 'total_amount' => 100000, 'paid_at' => $inLastYear,
        ]);

        app(CloseChampionPeriodAction::class)->execute($lastYear);

        $champion = LoyaltyChampion::query()->sole();
        $this->assertSame($buyer->id, $champion->user_id);
        $this->assertSame('PS5', $champion->grand_prize);
        $this->assertSame('pending', $champion->grand_prize_status);
    }

    public function test_command_skips_periods_that_ended_before_the_feature_was_enabled(): void
    {
        $user = User::factory()->create();
        $this->earn($user, 500, LeaderboardPeriod::make('month')->previous()->startsAt->copy()->addDay());

        $this->artisan('loyalty:close-champion-periods')->assertSuccessful();

        $this->assertSame(0, LoyaltyPeriodClosure::query()->count());
        $this->assertNotNull(app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class)->get('champions_enabled_since'));
    }

    public function test_purchases_earn_raffle_tickets_and_a_refund_cancels_them(): void
    {
        $campaignId = $this->openCampaign();
        app(UpdateSettingAction::class)->execute('loyalty', 'club_raffle_campaign_id', $campaignId);
        app(UpdateSettingAction::class)->execute('loyalty', 'purchase_raffle_toman_per_ticket', 200000);

        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id, 'order_number' => 'R-' . uniqid(), 'status' => OrderStatusEnum::PENDING_PAYMENT,
            'subtotal_amount' => 1000000, 'discount_amount' => 0, 'total_amount' => 1000000,
        ]);
        app(UpdateOrderStatusAction::class)->execute($order, OrderStatusEnum::PAID);

        $this->assertSame(5, DB::table('draw_tickets')->where('user_id', $user->id)->where('status', 'active')->count());

        app(UpdateOrderStatusAction::class)->execute($order->fresh(), OrderStatusEnum::REFUNDED, OrderRefundMethodEnum::MANUAL_BANK_TRANSFER, refundAmount: 0);

        $this->assertSame(0, DB::table('draw_tickets')->where('user_id', $user->id)->where('status', 'active')->count());
    }

    public function test_elite_membership_follows_the_configured_level(): void
    {
        LoyaltyLevel::query()->create(['title' => 'تازه‌وارد', 'min_xp' => 0, 'reward_points' => 0, 'purchase_multiplier' => 1, 'color_key' => 'slate', 'sort_order' => 1, 'is_active' => true]);
        $elite = LoyaltyLevel::query()->create(['title' => 'اسطوره', 'min_xp' => 1000, 'reward_points' => 0, 'purchase_multiplier' => 1, 'color_key' => 'rose', 'sort_order' => 2, 'is_active' => true]);
        $membership = app(ClubEliteMembership::class);
        $user = User::factory()->create();
        $this->earn($user, 999);

        $this->assertFalse($membership->enabled());
        app(UpdateSettingAction::class)->execute('loyalty', 'elite_min_level_id', $elite->id);
        Cache::flush();
        $this->assertFalse(app(ClubEliteMembership::class)->isMember($user));

        $this->earn($user, 1);
        $this->assertTrue(app(ClubEliteMembership::class)->isMember($user));
    }

    public function test_hall_of_fame_and_member_profile_pages_render(): void
    {
        $lastMonth = LeaderboardPeriod::make('month')->previous();
        $winner = User::factory()->create(['display_name' => 'Champion member']);
        $this->earn($winner, 800, $lastMonth->startsAt->copy()->addDay());
        app(CloseChampionPeriodAction::class)->execute($lastMonth);

        $this->get(route('loyalty.hall-of-fame'))
            ->assertSuccessful()
            ->assertSee('قهرمانان باشگاه')
            ->assertSee('Champion member');

        $this->actingAs($winner)->get(route('loyalty.hall-of-fame'))->assertSuccessful();

        $this->get(route('loyalty.members.show', $winner))
            ->assertSuccessful()
            ->assertSee('Champion member')
            ->assertSee('برترین ماه');

        $this->get(route('loyalty.members.show', 999999))->assertNotFound();
    }

    private function earn(User $user, int $amount, $at = null): void
    {
        app(AwardPointsAction::class)->execute(new AwardPointsDTO(
            account: app(GetOrCreatePointAccountAction::class)->execute($user),
            amount: $amount,
            reason: 'test_activity',
            idempotencyKey: 'test-earn:' . $user->id . ':' . uniqid(),
            reference: $user,
            occurredAt: $at,
        ));
    }

    private function staffUser(): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'title' => 'Staff ' . uniqid(), 'is_super_admin' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    private function openCampaign(): int
    {
        $values = [
            'title' => 'قرعه‌کشی باشگاه', 'slug' => 'club-' . uniqid(), 'status' => 'open',
            'starts_at' => now()->subDay(), 'closes_at' => now()->addMonth(),
            'created_at' => now(), 'updated_at' => now(),
        ];
        foreach (DB::select("PRAGMA table_info('draw_campaigns')") as $column) {
            if ($column->pk || ! $column->notnull || $column->dflt_value !== null || array_key_exists($column->name, $values)) {
                continue;
            }
            $type = strtolower((string) $column->type);
            $values[$column->name] = match (true) {
                str_contains($type, 'int'), str_contains($type, 'numeric') => 0,
                str_contains($type, 'date'), str_contains($type, 'time') => now(),
                default => '',
            };
        }
        DB::statement('PRAGMA defer_foreign_keys = ON');

        return (int) DB::table('draw_campaigns')->insertGetId($values);
    }
}
