<?php

namespace App\Application\Launcher\Actions;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Application\Loyalty\Actions\ResolveUserLevelAction;
use App\Application\Loyalty\Actions\ResolveVipPointsBonusAction;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * خلاصه‌ی باشگاه مخاطبان و اشتراک ویژه برای کارت پروفایل لانچر: امتیاز، سطح
 * و پیشرفت تا سطح بعد، رتبه در جدول امتیازات، و وضعیت VIP (با پاداش امتیازش).
 * فقط خواندنی است و هیچ امتیاز یا زنجیره‌ای ثبت نمی‌کند.
 */
class GetLauncherClubSummaryAction
{
    public function __construct(
        private readonly ResolveUserLevelAction $resolveUserLevel,
        private readonly GetAudienceClubLeaderboardAction $leaderboard,
        private readonly ResolveVipPointsBonusAction $vipPointsBonus,
    ) {
    }

    /** @return array<string, mixed> */
    public function execute(User $user): array
    {
        $account = $user->pointAccount()->first();
        $xp = (int) ($account?->lifetime_xp ?? 0);
        $level = $this->resolveUserLevel->execute($account);
        $next = $this->resolveUserLevel->nextLevel($account);
        $levels = $this->resolveUserLevel->activeLevels();
        $levelIndex = $levels->search(fn($candidate) => $candidate->is($level));
        $board = $this->leaderboard->execute($user, 'all');

        $vipExpiresAt = $user->vipExpiresAt();
        $vipActive = $vipExpiresAt !== null;

        return [
            'points' => [
                'balance' => max(0, (int) ($account?->balance ?? 0)),
                'lifetime_earned' => (int) ($account?->lifetime_earned ?? 0),
            ],
            'level' => [
                'title' => (string) $level->title,
                'badge' => $level->badge,
                'color_key' => $level->color_key ?: 'brand',
                'number' => $levelIndex === false ? 1 : $levelIndex + 1,
                'total' => max(1, $levels->count()),
                'xp' => $xp,
                'next_title' => $next?->title,
                'next_min_xp' => $next?->min_xp,
                'xp_to_next' => $next ? max(0, (int) $next->min_xp - $xp) : 0,
                'progress_percent' => $this->resolveUserLevel->progressPercent($account),
            ],
            'rank' => [
                'position' => $board['currentMember']['rank'] ?? null,
                'members' => (int) ($board['stats']['members'] ?? 0),
            ],
            'vip' => [
                'active' => $vipActive,
                'expires_at' => $vipExpiresAt?->toISOString(),
                'days_left' => $vipActive ? max(0, (int) ceil(now()->diffInHours($vipExpiresAt) / 24)) : 0,
                // درصد پاداش امتیاز VIP؛ برای کاربر غیر VIP هم برمی‌گردد تا لانچر
                // بتواند مزیت خرید اشتراک را نشان دهد.
                'points_bonus_percent' => $this->vipPointsBonus->percent(),
                'page_url' => Route::has('vip.index') ? route('vip.index') : null,
            ],
            'club_url' => Route::has('account.loyalty') ? route('account.loyalty') : null,
        ];
    }
}
