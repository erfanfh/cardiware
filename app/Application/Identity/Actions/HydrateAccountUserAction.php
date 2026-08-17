<?php

namespace App\Application\Identity\Actions;

use App\Domain\Identity\Models\User;

class HydrateAccountUserAction
{
    public function __construct(
        private readonly GetAccountUserStatsAction $getAccountUserStatsAction,
        private readonly GetUserAvatarAction       $getUserAvatarAction,
    )
    {
    }

    public function execute(User $user): User
    {
        $stats = $this->getAccountUserStatsAction->execute($user);
        $avatar = $this->getUserAvatarAction->execute($user);

        $user->setAttribute('paid_orders_count', $stats['paid_orders_count']);
        $user->setAttribute('purchase_accesses_count', $stats['purchase_accesses_count']);
        $user->setAttribute('favorite_products_count', $stats['favorite_products_count']);
        $user->setAttribute('tickets_count', $stats['tickets_count']);
        $user->setAttribute('open_tickets_count', $stats['open_tickets_count']);
        $user->setAttribute('avatar_url', $avatar['url']);

        $pointAccount = $user->pointAccount()->first(['balance']);
        $user->setAttribute('loyalty_points', max(0, (int)($pointAccount?->balance ?? 0)));

        return $user;
    }
}
