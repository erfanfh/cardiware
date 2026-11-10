<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetAudienceClubOverviewAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\Actions\SpendPointsAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * پروفایل اختصاصی VIP، نشان VIP در جدول برترین‌ها و گزارش مصرف امتیاز در پنل کاربر.
 */
class VipClubProfileAndPointsReportTest extends TestCase
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

    public function test_vip_member_bio_is_public_only_after_moderation(): void
    {
        $user = User::factory()->create(['username' => 'vipfan']);
        $this->giveVip($user);

        $this->actingAs($user)
            ->put(route('account.loyalty.vip-profile.update'), ['club_bio' => 'طرفدار مادهای لیگ برتر', 'club_theme' => 'violet'])
            ->assertSessionHasNoErrors();

        $this->assertSame('violet', $user->fresh()->club_theme);
        $this->assertSame('pending', $user->fresh()->club_bio_status);

        auth()->logout();
        $this->get(route('loyalty.members.show', $user))
            ->assertOk()
            ->assertDontSee('طرفدار مادهای لیگ برتر');

        $user->forceFill(['club_bio_status' => 'approved'])->save();
        $this->get(route('loyalty.members.show', $user))
            ->assertOk()
            ->assertSee('طرفدار مادهای لیگ برتر')
            ->assertSee('عضو VIP')
            ->assertSee('from-violet-400', false);
    }

    public function test_links_and_phone_numbers_are_refused_in_the_bio(): void
    {
        $user = User::factory()->create();
        $this->giveVip($user);

        foreach (['سایت من https://spam.example', 'تماس 09121234567', 'www.example.com'] as $bio) {
            $this->actingAs($user)
                ->put(route('account.loyalty.vip-profile.update'), ['club_bio' => $bio, 'club_theme' => 'gold'])
                ->assertSessionHasErrors('club_bio');
        }
        $this->assertNull($user->fresh()->club_bio);
    }

    public function test_regular_member_cannot_save_and_an_expired_vip_bio_is_hidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.loyalty.vip-profile.update'), ['club_bio' => 'سلام', 'club_theme' => 'gold'])
            ->assertSessionHasErrors('club_bio');
        $this->assertNull($user->fresh()->club_bio);

        $user->forceFill(['club_bio' => 'متن قدیمی VIP', 'club_theme' => 'night'])->save();
        $this->get(route('loyalty.members.show', $user))->assertOk()->assertDontSee('متن قدیمی VIP')->assertDontSee('عضو VIP');
    }

    public function test_leaderboard_shows_the_vip_badge_only_for_vip_members(): void
    {
        $vip = User::factory()->create(['display_name' => 'VipLeader']);
        $regular = User::factory()->create(['display_name' => 'RegularLeader']);
        $this->giveVip($vip);
        $this->earn($vip, 500);
        $this->earn($regular, 300);

        $html = $this->get(route('loyalty.leaderboard'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, route('loyalty.members.show', $vip) . '"' . "\n    title=\"کاربر دارای اشتراک VIP فعال"));
        $this->assertStringNotContainsString(route('loyalty.members.show', $regular) . '"' . "\n    title=\"کاربر دارای اشتراک VIP فعال", $html);
    }

    public function test_account_page_reports_spending_by_use_vip_bonus_and_filters_history(): void
    {
        app(UpdateSettingAction::class)->execute('loyalty', 'vip_points_bonus_percent', 50);
        $user = User::factory()->create();
        $this->giveVip($user);
        $account = app(GetOrCreatePointAccountAction::class)->execute($user);

        $this->earn($user, 1000);
        $this->earn($user, 150, ['vip_bonus_percent' => 50, 'points_before_vip_bonus' => 100]);
        $coupon = $this->offer(RewardTypeEnum::COUPON, 300);
        $raffle = $this->offer(RewardTypeEnum::RAFFLE_TICKET, 100);
        $this->spend($account, 300, 'reward_redemption', $coupon);
        $this->spend($account, 100, 'reward_redemption', $raffle);
        $this->spend($account, 100, 'reward_redemption', $raffle);
        $this->spend($account, 200, 'wallet_conversion');

        $club = app(GetAudienceClubOverviewAction::class)->execute($user->fresh());
        $breakdown = collect($club['spendBreakdown'])->keyBy('key');

        $this->assertSame(300, $breakdown['coupon']['points']);
        $this->assertSame(200, $breakdown['raffle']['points']);
        $this->assertSame(2, $breakdown['raffle']['count']);
        $this->assertSame(200, $breakdown['wallet']['points']);
        $this->assertSame(50, $club['vip']['bonusEarned']);
        $this->assertSame(450, $club['spendablePointBalance']);

        $spent = app(GetAudienceClubOverviewAction::class)->execute($user->fresh(), 'spent');
        $this->assertCount(4, $spent['pointEntries']->items());
        $earned = app(GetAudienceClubOverviewAction::class)->execute($user->fresh(), 'earned');
        $this->assertCount(2, $earned['pointEntries']->items());

        $this->actingAs($user)->get(route('account.loyalty'))
            ->assertOk()
            ->assertSee('امتیازهایتان کجا رفت؟')
            ->assertSee('خرید کد تخفیف')
            ->assertSee('ثبت کد قرعه‌کشی')
            ->assertSee('پروفایل اختصاصی شما');
        $this->actingAs($user)->get(route('account.loyalty', ['points_filter' => 'spent']))->assertOk();
    }

    private function earn(User $user, int $amount, array $metadata = []): void
    {
        app(AwardPointsAction::class)->execute(new AwardPointsDTO(
            account: app(GetOrCreatePointAccountAction::class)->execute($user),
            amount: $amount,
            reason: 'test_activity',
            idempotencyKey: 'vip-report:' . $user->id . ':' . uniqid(),
            reference: $user,
            metadata: $metadata,
        ));
    }

    private function spend($account, int $amount, string $reason, ?RewardOffer $offer = null): void
    {
        app(SpendPointsAction::class)->execute(new SpendPointsDTO(
            account: $account->fresh(),
            amount: $amount,
            reason: $reason,
            idempotencyKey: 'vip-report-spend:' . uniqid(),
            reference: $offer,
            description: 'test spend',
        ));
    }

    private function offer(RewardTypeEnum $type, int $cost): RewardOffer
    {
        return RewardOffer::query()->create([
            'code' => 'offer-' . uniqid(),
            'title' => 'Offer ' . $type->value,
            'type' => $type,
            'points_cost' => $cost,
            'is_active' => true,
            'sort_order' => 1,
            'config' => $type === RewardTypeEnum::COUPON ? ['coupon_type' => 'percentage', 'coupon_value' => 10] : [],
        ]);
    }

    private function giveVip(User $user): void
    {
        DB::statement('PRAGMA defer_foreign_keys = ON');
        DB::table('vip_subscriptions')->insert([
            'user_id' => $user->id,
            'vip_plan_id' => 1,
            'order_id' => 1,
            'order_item_id' => $user->id,
            'duration_days' => 30,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(20),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
