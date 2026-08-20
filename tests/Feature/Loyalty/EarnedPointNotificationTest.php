<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Notifications\PendingLoyaltyEarnedNotifications;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EarnedPointNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_visit_notification_is_claimed_once_and_only_for_a_new_award(): void
    {
        $user = User::factory()->create();
        $this->createRule(EarningRuleTypeEnum::DAILY_VISIT, [
            'points' => 7,
        ]);

        $this->actingAs($user)
            ->get(route('loyalty.leaderboard'))
            ->assertSuccessful()
            ->assertCookie(PendingLoyaltyEarnedNotifications::CLIENT_COOKIE);

        $this->postJson(route('loyalty.earned-notifications.index'))
            ->assertSuccessful()
            ->assertJsonPath('notifications.0.type', 'daily_visit')
            ->assertJsonPath('notifications.0.points', 7)
            ->assertJsonPath('notifications.0.balance_after', 7)
            ->assertJsonPath('notifications.0.action_label', 'اولین ورود امروز');

        $this->postJson(route('loyalty.earned-notifications.index'))
            ->assertExactJson(['notifications' => []]);

        // Simulate a cache restart: ledger idempotency must still prevent both a
        // duplicate credit and a second congratulatory notification.
        Cache::clear();

        $this->get(route('loyalty.leaderboard'))->assertSuccessful();

        $this->postJson(route('loyalty.earned-notifications.index'))
            ->assertExactJson(['notifications' => []]);

        $this->assertDatabaseCount('point_ledger_entries', 1);
    }

    public function test_time_spent_completion_returns_a_rich_notification_only_once(): void
    {
        $user = User::factory()->create();
        $this->createRule(EarningRuleTypeEnum::TIME_SPENT, [
            'points' => 11,
            'required_seconds' => 10,
            'daily_limit' => 5,
            'targets' => ['product', 'post'],
            'unique_target_daily' => true,
        ]);

        $start = $this->actingAs($user)->postJson(
            route('loyalty.time-spent.start'),
            [
                'target_type' => 'post',
                'target_id' => 123,
            ],
        );

        $start
            ->assertSuccessful()
            ->assertJsonPath('required_seconds', 10);

        $this->travel(11)->seconds();

        $complete = $this->postJson(
            route('loyalty.time-spent.complete'),
            ['token' => $start->json('token')],
        );

        $complete
            ->assertSuccessful()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('earned_points', 11)
            ->assertJsonPath('notification.type', 'time_spent')
            ->assertJsonPath('notification.points', 11)
            ->assertJsonPath('notification.balance_after', 11)
            ->assertJsonPath('notification.required_seconds', 10)
            ->assertJsonPath('notification.action_label', 'مطالعه این نوشته');

        $this->postJson(
            route('loyalty.time-spent.complete'),
            ['token' => $start->json('token')],
        )
            ->assertSuccessful()
            ->assertJsonPath('earned_points', 0)
            ->assertJsonPath('notification', null);

        $this->assertSame(1, PointLedgerEntry::query()->count());
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function createRule(EarningRuleTypeEnum $type, array $config): EarningRule
    {
        return EarningRule::query()->create([
            'code' => "test-{$type->value}",
            'title' => "Test {$type->value}",
            'type' => $type,
            'is_active' => true,
            'priority' => 100,
            'config' => $config,
        ]);
    }
}
