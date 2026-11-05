<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\Actions\ReverseEarnedPointsForReferenceAction;
use App\Application\Loyalty\Actions\SpendPointsAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * جدول برترین‌ها: رتبه با XP کسب‌شده در دوره، نه موجودی قابل خرج.
 */
class AudienceClubLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
    }

    public function test_it_ranks_active_non_staff_members_by_xp_earned_this_month(): void
    {
        $current = User::factory()->create(['display_name' => 'Current member']);
        $first = User::factory()->create(['display_name' => 'First member']);
        $second = User::factory()->create(['display_name' => 'Second member']);
        $inactive = User::factory()->create(['display_name' => 'Inactive member', 'status' => UserStatusEnum::INACTIVE]);
        $staff = $this->staffUser();

        $this->earn($current, 300);
        $this->earn($first, 500);
        $this->earn($second, 400);
        $this->earn($inactive, 9000);
        $this->earn($staff, 9000);

        $board = app(GetAudienceClubLeaderboardAction::class)->execute($current);

        $this->assertSame([$first->id, $second->id, $current->id], $board['leaders']->pluck('user_id')->all());
        $this->assertSame([500, 400, 300], $board['leaders']->pluck('leaderboard_score')->all());
        $this->assertSame(3, $board['currentMember']['rank']);
        $this->assertSame(300, $board['currentMember']['score']);
        $this->assertSame(3, $board['stats']['members']);
        $this->assertSame(1200, $board['stats']['total_points']);

        $staffView = app(GetAudienceClubLeaderboardAction::class)->execute($staff);
        $this->assertTrue($staffView['currentMember']['is_staff']);
        $this->assertNull($staffView['currentMember']['rank']);
    }

    public function test_spending_points_keeps_the_rank_but_a_reversal_lowers_it(): void
    {
        $spender = User::factory()->create();
        $other = User::factory()->create();
        $this->earn($spender, 500);
        $this->earn($other, 400);

        // خرج کردن امتیاز (جایزه/تبدیل) رتبه را پایین نمی‌آورد.
        app(SpendPointsAction::class)->execute(new SpendPointsDTO(
            account: app(GetOrCreatePointAccountAction::class)->execute($spender),
            amount: 450,
            reason: 'reward_redeem',
            idempotencyKey: 'test-spend:' . $spender->id,
        ));
        Cache::flush();
        $this->assertSame($spender->id, app(GetAudienceClubLeaderboardAction::class)->execute(null)['leaders']->first()->user_id);

        // ولی برگشت امتیاز (مثلاً مرجوعی) از امتیاز دوره کم می‌شود.
        app(ReverseEarnedPointsForReferenceAction::class)->execute(
            user: $spender,
            reference: $spender,
            reason: 'test_activity',
            eventKey: 'test-reverse:' . $spender->id,
        );
        Cache::flush();
        $board = app(GetAudienceClubLeaderboardAction::class)->execute($spender);
        $this->assertSame([$other->id], $board['leaders']->pluck('user_id')->all());
        $this->assertNull($board['currentMember']['rank']);
    }

    public function test_previous_months_do_not_count_for_this_month_but_count_for_all_time(): void
    {
        $old = User::factory()->create();
        $recent = User::factory()->create();
        $this->earn($old, 1000, now()->subMonths(2));
        $this->earn($recent, 100);

        $month = app(GetAudienceClubLeaderboardAction::class)->execute(null, 'month');
        $all = app(GetAudienceClubLeaderboardAction::class)->execute(null, 'all');

        $this->assertSame([$recent->id], $month['leaders']->pluck('user_id')->all());
        $this->assertSame([$old->id, $recent->id], $all['leaders']->pluck('user_id')->all());
    }

    public function test_guest_and_member_can_view_every_period_tab(): void
    {
        $this->get(route('loyalty.leaderboard'))
            ->assertSuccessful()
            ->assertSee('برترین‌های باشگاه مخاطبان')
            ->assertSee('ورود و مشاهده رتبه من');

        $user = User::factory()->create(['display_name' => 'Leaderboard member']);
        $this->earn($user, 250);

        foreach (['month', 'season', 'year', 'all'] as $period) {
            $this->actingAs($user)
                ->get(route('loyalty.leaderboard', ['period' => $period]))
                ->assertSuccessful()
                ->assertSee('Leaderboard member');
        }
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

        return User::factory()->create(['role_id' => $roleId, 'display_name' => 'Staff member']);
    }
}
