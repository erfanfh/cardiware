<?php

namespace App\Application\Loyalty\Listeners;

use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Loyalty\Models\RegistrationPointEligibility;
use Throwable;

class RecordRegistrationPointEligibilityListener
{
    public function handle(UserRegisteredEvent $event): void
    {
        try {
            RegistrationPointEligibility::query()->firstOrCreate(
                ['user_id' => $event->userId],
                ['occurred_at' => $event->occurredAt],
            );
        } catch (Throwable $exception) {
            // This audit marker improves recoverability but must never break the
            // already-committed registration flow.
            report($exception);
        }
    }
}
