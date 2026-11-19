<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Achievements\Actions\EvaluateUserAchievementsAction;
use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\Actions\RevokePurchaseGamificationForOrderAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Poll\Actions\SubmitPollVoteAction;
use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\AchievementMetricEnum;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Poll\Models\Poll;
use Database\Seeders\Loyalty\LoyaltyDiverseAchievementsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DiverseAchievementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_is_repeatable_and_all_metrics_have_labels(): void
    {
        $this->seed(LoyaltyDiverseAchievementsSeeder::class);
        $this->seed(LoyaltyDiverseAchievementsSeeder::class);
        $this->assertDatabaseCount('loyalty_achievements', 11);
        foreach (AchievementMetricEnum::cases() as $metric) {
            $this->assertNotEmpty($metric->label());
        }
    }

    public function test_fifth_poll_awards_once_and_changing_a_vote_adds_no_reward(): void
    {
        $this->seed(LoyaltyDiverseAchievementsSeeder::class);
        $user = User::factory()->create();
        $identity = PollVoterIdentity::resolve($user, '203.0.113.10');
        for ($i = 0; $i < 5; $i++) {
            $poll = Poll::query()->create([
                'title' => 'Poll '.$i, 'slug' => 'achievement-poll-'.$i, 'question' => 'Choose',
                'status' => 'published', 'audience' => 'everyone', 'results_visibility' => 'after_vote',
                'min_selections' => 1, 'max_selections' => 1, 'allow_vote_changes' => true,
            ]);
            $option = $poll->options()->create(['label' => 'Yes', 'is_active' => true]);
            app(SubmitPollVoteAction::class)->execute($poll, $identity, [$option->id]);
            $this->assertSame($i === 4 ? 1 : 0, PointLedgerEntry::query()->where('reason', 'achievement_earned')->count());
        }
        app(SubmitPollVoteAction::class)->execute($poll->fresh(), $identity, [$option->id]);
        $this->assertSame(100, $user->pointAccount()->value('balance'));
        $this->assertSame(0, $user->pointAccount()->value('lifetime_xp'));
        $this->assertSame(1, PointLedgerEntry::query()->where('reason', 'achievement_earned')->count());
    }

    public function test_daily_visit_badge_counts_distinct_event_days_and_includes_today(): void
    {
        Cache::flush();
        $this->travelTo(now()->startOfDay()->addHours(12));
        $user = User::factory()->create();
        $badge = $this->badge(AchievementMetricEnum::DAILY_VISIT_DAYS, 3);
        $account = app(GetOrCreatePointAccountAction::class)->execute($user);
        foreach ([2, 2, 1] as $index => $daysAgo) {
            app(AwardPointsAction::class)->execute(new AwardPointsDTO(
                account: $account, amount: 1, reason: 'daily_visit', idempotencyKey: 'historical-visit-'.$index,
                occurredAt: now()->subDays($daysAgo),
            ));
        }
        app(EvaluateUserAchievementsAction::class)->execute($user);
        $this->assertFalse($badge->users()->whereKey($user->id)->exists());

        EarningRule::query()->create([
            'code' => 'test-daily-visit', 'title' => 'Visit', 'type' => EarningRuleTypeEnum::DAILY_VISIT,
            'is_active' => true, 'priority' => 100, 'config' => ['points' => 1],
        ]);
        $this->actingAs($user)->get(route('loyalty.leaderboard'))->assertSuccessful();
        $this->assertTrue($badge->users()->whereKey($user->id)->exists());
        $this->get(route('loyalty.leaderboard'))->assertSuccessful();
        $this->assertSame(1, PointLedgerEntry::query()->where('reason', 'achievement_earned')->count());
    }

    public function test_collector_counts_unique_owned_products_and_refunds_revoke_the_reward(): void
    {
        $user = User::factory()->create();
        $badge = $this->badge(AchievementMetricEnum::PRODUCTS_OWNED, 2);
        $storeId = DB::table('stores')->insertGetId(['title' => 'Store', 'slug' => 'collector-store', 'status' => 'active']);
        $order = Order::query()->create([
            'user_id' => $user->id, 'order_number' => 'COLLECTOR', 'status' => OrderStatusEnum::PAID,
            'subtotal_amount' => 100, 'total_amount' => 100,
        ]);
        $accesses = [];
        for ($i = 0; $i < 5; $i++) {
            $productId = DB::table('products')->insertGetId([
                'store_id' => $storeId, 'title' => 'Product '.$i, 'slug' => 'collector-'.$i,
                'type' => 'digital', 'structure' => 'simple', 'status' => 'active',
                'published_at' => now(),
            ]);
            $saleItem = ProductSaleItem::query()->create([
                'product_id' => $productId, 'title' => 'Edition', 'status' => 'available', 'base_price' => 100,
            ]);
            $accesses[] = PurchaseAccess::query()->create([
                'user_id' => $user->id, 'product_id' => $productId, 'product_sale_item_id' => $saleItem->id,
                'order_id' => $order->id, 'status' => 'active',
            ]);
        }
        $accesses[1]->update(['metadata' => ['source' => PurchaseAccess::SOURCE_VIP_BORROW]]);
        $accesses[2]->update(['status' => 'revoked']);
        $accesses[3]->update(['expires_at' => now()->subDay()]);
        $accesses[4]->update(['starts_at' => now()->addDay()]);
        PurchaseAccess::query()->create([
            'user_id' => $user->id, 'product_id' => $accesses[0]->product_id, 'order_id' => $order->id, 'status' => 'active',
            'product_sale_item_id' => $accesses[0]->product_sale_item_id,
        ]);
        app(EvaluateUserAchievementsAction::class)->execute($user);
        $this->assertFalse($badge->users()->whereKey($user->id)->exists());
        $accesses[4]->update(['starts_at' => now()->subMinute()]);
        app(EvaluateUserAchievementsAction::class)->execute($user);
        $this->assertTrue($badge->users()->whereKey($user->id)->exists());
        $this->assertSame(100, $user->pointAccount()->value('balance'));

        $order->update(['status' => OrderStatusEnum::REFUNDED]);
        PurchaseAccess::query()->withoutGlobalScopes()->where('order_id', $order->id)->update(['status' => 'revoked']);
        app(RevokePurchaseGamificationForOrderAction::class)->execute($order->fresh());
        $this->assertFalse($badge->users()->whereKey($user->id)->exists());
        $this->assertSame(0, $user->pointAccount()->value('balance'));
    }

    private function badge(AchievementMetricEnum $metric, int $threshold): LoyaltyAchievement
    {
        return LoyaltyAchievement::query()->create([
            'code' => $metric->value, 'title' => 'Test badge', 'metric' => $metric,
            'threshold' => $threshold, 'reward_points' => 100, 'is_active' => true,
        ]);
    }
}
