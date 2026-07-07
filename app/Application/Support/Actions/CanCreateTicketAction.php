<?php

namespace App\Application\Support\Actions;

use App\Domain\Identity\Models\User;

class CanCreateTicketAction
{
    public function execute(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->purchaseAccesses()->count() === 0) {
            return false;
        }

        return true;
    }
}
