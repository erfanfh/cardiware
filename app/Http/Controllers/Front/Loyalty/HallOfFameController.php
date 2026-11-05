<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Application\Loyalty\Support\ClubEliteMembership;
use App\Application\Loyalty\Support\LeaderboardPeriod;
use App\Domain\Commerce\Models\Order;
use App\Domain\Loyalty\Models\LoyaltyPeriodClosure;
use App\Domain\Poll\Enums\PollAudienceEnum;
use App\Domain\Poll\Models\Poll;
use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * تالار افتخار: برترین‌های هر ماه، فصل و سال، رقابت کاربر برتر امسال (جایزه‌ی ویژه و
 * حد نصاب)، جوایز هر رتبه و کانون نخبگان.
 */
final class HallOfFameController extends Controller
{
    public function __invoke(
        Request $request,
        LoyaltySettings $settings,
        GetAudienceClubLeaderboardAction $leaderboard,
        ClubEliteMembership $elite,
    ): View {
        $user = $request->user();

        // دوره‌ها روزی یک بار بسته می‌شوند؛ ۱۰ دقیقه کش کافی است.
        $closures = fn (string $type, int $limit) => Cache::remember(
            "loyalty:hall-of-fame:{$type}:{$limit}",
            GetAudienceClubLeaderboardAction::CACHE_SECONDS,
            fn () => LoyaltyPeriodClosure::query()
                ->where('period_type', $type)
                ->where('winners_count', '>', 0)
                ->with(['champions.user.avatar', 'champions.user.pointAccount:id,user_id,lifetime_xp'])
                ->latest('ends_at')
                ->limit($limit)
                ->get(),
        );

        $year = LeaderboardPeriod::make('year');
        $yearBoard = $leaderboard->execute($user, 'year');
        $yearRace = [
            'period' => $year,
            'grandPrize' => trim((string) $settings->get('champion_year_grand_prize', '')),
            'minXp' => (int) $settings->get('champion_year_min_xp', 20000),
            'minMissions' => (int) $settings->get('champion_year_min_missions', 20),
            'minOrders' => (int) $settings->get('champion_year_min_orders', 3),
            'leaders' => $yearBoard['leaders']->take(3),
            'me' => null,
        ];

        if ($user !== null) {
            $yearRace['me'] = [
                'xp' => $yearBoard['currentMember']['score'],
                'missions' => DB::table('loyalty_mission_progress')
                    ->where('user_id', $user->getKey())
                    ->whereNotNull('completed_at')
                    ->where('completed_at', '>=', $year->startsAt)
                    ->count(),
                'orders' => Order::query()->where('user_id', $user->getKey())->paid()
                    ->where('paid_at', '>=', $year->startsAt)->count(),
            ];
        }

        $elitePolls = $elite->enabled()
            ? Poll::query()->publiclyVisible()->where('audience', PollAudienceEnum::CLUB_ELITE->value)->latest('id')->limit(6)->get()
            : collect();

        return view('front.loyalty.hall-of-fame', [
            'user' => $user,
            'months' => $closures('month', 12),
            'seasons' => $closures('season', 8),
            'years' => $closures('year', 10),
            'yearRace' => $yearRace,
            'prizes' => [
                'month' => $settings->championPrizes('month'),
                'season' => $settings->championPrizes('season'),
                'year' => $settings->championPrizes('year'),
            ],
            'eliteLevel' => $elite->level(),
            'isEliteMember' => $elite->isMember($user),
            'elitePolls' => $elitePolls,
        ]);
    }
}
