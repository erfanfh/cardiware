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

        return true;
    }
}
