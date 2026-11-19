<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Application\Loyalty\Actions\ResolveUserLevelAction;
use App\Application\Loyalty\Support\ClubEliteMembership;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\LoyaltyAchievement;
use App\Domain\Loyalty\Models\ClubFollow;
use App\Domain\Loyalty\Models\LoyaltyChampion;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * کارت عمومی باشگاه هر عضو: سطح، XP، جام‌ها (برترین ماه/فصل/سال)، نشان‌ها، VIP و
 * عضویت کانون نخبگان. فقط اطلاعات باشگاه نمایش داده می‌شود، نه اطلاعات شخصی.
 */
final class MemberProfileController extends Controller
{
    public function __invoke(
        User $user,
        ResolveUserLevelAction $levels,
        ClubEliteMembership $elite,
        GetAudienceClubLeaderboardAction $leaderboard,
    ): View {
        abort_unless($user->status === UserStatusEnum::ACTIVE, 404);

        $user->loadMissing(['avatar', 'pointAccount']);
        $xp = (int) ($user->pointAccount?->lifetime_xp ?? 0);

        [$trophies, $achievements] = Cache::remember(
            'loyalty:member-card:' . $user->getKey(),
            GetAudienceClubLeaderboardAction::CACHE_SECONDS,
            fn (): array => [
                LoyaltyChampion::query()
                    ->where('user_id', $user->getKey())
                    ->orderByRaw("CASE period_type WHEN 'year' THEN 0 WHEN 'season' THEN 1 ELSE 2 END")
                    ->orderBy('rank')
                    ->latest('id')
                    ->get(),
                LoyaltyAchievement::query()
                    ->whereIn('id', DB::table('loyalty_achievement_user')->where('user_id', $user->getKey())->pluck('loyalty_achievement_id'))
                    ->orderBy('sort_order')
                    ->get(),
            ],
        );

        // امتیازات مهم کسب‌شده به تفکیک دلیل (برای نمایش عمومی)
        $earnedByReason = PointLedgerEntry::query()
            ->where('point_account_id', $user->pointAccount?->getKey())
            ->where('type', PointEntryTypeEnum::EARN)
            ->whereDoesntHave('reversals')
            ->selectRaw('reason, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('reason')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $labels = [
                    'daily_visit' => 'بازدید روزانه',
                    'comment' => 'ارسال دیدگاه',
                    'purchase' => 'خرید محصول',
                    'registration' => 'هدیه خوش‌آمدگویی',
                    'membership_anniversary' => 'سالگرد عضویت',
                    'gallery_submission' => 'ارسال رسانه در گالری',
                    'achievement_earned' => 'کسب نشان افتخار',
                    'mission_completed' => 'مأموریت هفتگی',
                    'referral_bonus' => 'پاداش دعوت دوستان',
                    'referral_sale' => 'خرید دوستان دعوت‌شده',
                    'level_up' => 'ارتقای سطح',
                    'time_spent' => 'فعالیت در سایت',
                    'streak_milestone' => 'زنجیره بازدید',
                    'champion_prize' => 'جایزه تالار افتخارات',
                ];

                return [
                    'label' => $labels[$row->reason] ?? $row->reason,
                    'total' => (int) $row->total,
                    'count' => (int) $row->count,
                ];
            });

        return view('front.loyalty.member', [
            'member' => $user,
            'xp' => $xp,
            'level' => $levels->execute($xp),
            'nextLevel' => $levels->nextLevel($xp),
            'progress' => $levels->progressPercent($xp),
            'trophies' => $trophies,
            'achievements' => $achievements,
            'isVip' => $isVip = $user->hasActiveVipSubscription(),
            'vipSince' => $isVip ? \App\Application\Loyalty\Support\VipClubProfile::activeSince($user) : null,
            'isElite' => $elite->enabled() && $elite->isMember($user),
            'monthRank' => $user->isLoyaltyStaff() ? null : $leaderboard->execute($user, 'month')['currentMember']['rank'],
            'earnedByReason' => $earnedByReason,
            'followersCount' => ClubFollow::query()->where('target_id', $user->getKey())->count(),
            'isFollowing' => auth()->check()
                && ClubFollow::query()
                    ->where('follower_id', auth()->id())
                    ->where('target_id', $user->getKey())
                    ->exists(),
        ]);
    }
}
