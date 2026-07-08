<?php

namespace App\Application\Support\Observers;

use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Models\Ticket;

class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        $this->flushUserCache($ticket);
    }

    public function updated(Ticket $ticket): void
    {
        $this->flushUserCache($ticket);

        if ($ticket->wasChanged('user_id')) {
            $this->flushOriginalUserCache($ticket);
        }
    }

    private function flushUserCache(Ticket $ticket): void
    {
        $user = $ticket->user;

        if (!$user) {
            return;
        }

        app(UserCacheInvalidator::class)->flushForUser($user);
    }

    private function flushOriginalUserCache(Ticket $ticket): void
    {
        $originalUserId = $ticket->getOriginal('user_id');

        if (!$originalUserId) {
            return;
        }

        $user = User::query()->find($originalUserId);

        if (!$user) {
            return;
        }

        app(UserCacheInvalidator::class)->flushForUser($user);
    }
}
