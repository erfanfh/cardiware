<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudienceClubLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ranks_active_members_by_balance_and_lifetime_points(): void
    {
        $currentUser = User::factory()->create(['display_name' => 'Current member']);
        $firstUser = User::factory()->create(['display_name' => 'First member']);
        $secondUser = User::factory()->create(['display_name' => 'Second member']);
        $inactiveUser = User::factory()->create([
            'display_name' => 'Inactive member',
            'status' => UserStatusEnum::INACTIVE,
        ]);
        $zeroBalanceUser = User::factory()->create(['display_name' => 'Zero balance member']);

        $this->createPointAccount($currentUser, 300, 600);
        $this->createPointAccount($firstUser, 500, 1_000);
        $this->createPointAccount($secondUser, 500, 900);
        $this->createPointAccount($inactiveUser, 9_000, 9_000);
        $this->createPointAccount($zeroBalanceUser, 0, 100);

        $leaderboard = app(GetAudienceClubLeaderboardAction::class)->execute($currentUser);

        $this->assertSame(
            [$firstUser->id, $secondUser->id, $currentUser->id],
            $leaderboard['leaders']->pluck('user_id')->all(),
        );
        $this->assertSame([1, 2, 3], $leaderboard['leaders']->pluck('leaderboard_rank')->all());
        $this->assertSame(3, $leaderboard['currentMember']['rank']);
        $this->assertTrue($leaderboard['currentMember']['is_visible']);
        $this->assertSame(3, $leaderboard['stats']['members']);
        $this->assertSame(1_300, $leaderboard['stats']['total_points']);
        $this->assertSame(500, $leaderboard['stats']['highest_points']);
    }

    public function test_guest_can_view_the_public_leaderboard_page(): void
    {
        $this->get(route('loyalty.leaderboard'))
            ->assertSuccessful()
            ->assertSee('برترین‌های باشگاه مخاطبان')
            ->assertSee('ورود و مشاهده رتبه من');
    }

    public function test_authenticated_member_can_view_the_leaderboard_page(): void
    {
        $user = User::factory()->create(['display_name' => 'Leaderboard member']);
        $this->createPointAccount($user, 250, 400);

        $this->actingAs($user)
            ->get(route('loyalty.leaderboard'))
            ->assertSuccessful()
            ->assertSee('برترین‌های باشگاه مخاطبان')
            ->assertSee('Leaderboard member');
    }

    private function createPointAccount(User $user, int $balance, int $lifetimeEarned): PointAccount
    {
        $account = new PointAccount;
        $account->forceFill([
            'user_id' => $user->getKey(),
            'balance' => $balance,
            'lifetime_earned' => $lifetimeEarned,
            'lifetime_spent' => max(0, $lifetimeEarned - $balance),
        ]);
        $account->save();

        return $account;
    }
}
