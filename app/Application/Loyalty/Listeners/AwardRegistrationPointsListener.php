<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\RegistrationEarningContext;
use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Throwable;

class AwardRegistrationPointsListener
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
    ) {
    }

    public function handle(UserRegisteredEvent $event): void
    {
        try {
            $user = User::query()->find($event->userId);
            if (!$user) {
                return;
            }

            $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::REGISTRATION,
                new RegistrationEarningContext(
                    user: $user,
                    occurredAt: $event->occurredAt,
                ),
            );
        } catch (Throwable $exception) {
            // The exact public-registration eligibility is persisted by a separate
            // synchronous listener, so reconciliation can safely retry later.
            report($exception);
        }
    }
}
