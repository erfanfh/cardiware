<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Database\Eloquent\Builder;

class GetAudienceClubLeaderboardAction
{
    private const LEADERBOARD_LIMIT = 50;

    /** @return array<string, mixed> */
    public function execute(?User $user): array
    {
        $leaders = $this->rankedAccountsQuery()
            ->with('user.avatar')
            ->limit(self::LEADERBOARD_LIMIT)
            ->get()
            ->values();

        $leaders->each(
            fn (PointAccount $account, int $index) => $account->setAttribute('leaderboard_rank', $index + 1),
        );

        $currentAccount = $user?->pointAccount()->first();
        $currentRank = $currentAccount && $currentAccount->balance > 0
            ? $this->rankFor($currentAccount)
            : null;

        $eligibleAccounts = $this->eligibleAccountsQuery();
        $totalMembers = (clone $eligibleAccounts)->count();
        $totalPoints = (int) (clone $eligibleAccounts)->sum('balance');

        return [
            'leaders' => $leaders,
            'visibleLimit' => self::LEADERBOARD_LIMIT,
            'stats' => [
                'members' => $totalMembers,
                'total_points' => $totalPoints,
                'average_points' => $totalMembers > 0 ? (int) floor($totalPoints / $totalMembers) : 0,
                'highest_points' => (int) ($leaders->first()?->balance ?? 0),
            ],
            'currentMember' => [
                'rank' => $currentRank,
                'balance' => max(0, (int) ($currentAccount?->balance ?? 0)),
                'lifetime_earned' => (int) ($currentAccount?->lifetime_earned ?? 0),
                'is_visible' => $currentRank !== null && $currentRank <= self::LEADERBOARD_LIMIT,
            ],
        ];
    }

    private function rankFor(PointAccount $account): int
    {
        return $this->eligibleAccountsQuery()
            ->where(function (Builder $query) use ($account): void {
                $query
                    ->where('balance', '>', $account->balance)
                    ->orWhere(function (Builder $query) use ($account): void {
                        $query
                            ->where('balance', $account->balance)
                            ->where('lifetime_earned', '>', $account->lifetime_earned);
                    })
                    ->orWhere(function (Builder $query) use ($account): void {
                        $query
                            ->where('balance', $account->balance)
                            ->where('lifetime_earned', $account->lifetime_earned)
                            ->where('id', '<', $account->getKey());
                    });
            })
            ->count() + 1;
    }

    /** @return Builder<PointAccount> */
    private function rankedAccountsQuery(): Builder
    {
        return $this->eligibleAccountsQuery()
            ->orderByDesc('balance')
            ->orderByDesc('lifetime_earned')
            ->orderBy('id');
    }

    /** @return Builder<PointAccount> */
    private function eligibleAccountsQuery(): Builder
    {
        return PointAccount::query()
            ->where('balance', '>', 0)
            ->whereHas('user', function (Builder $query): void {
                $query->where('status', UserStatusEnum::ACTIVE->value);
            });
    }
}
