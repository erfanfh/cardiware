<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Actions\Reward\GrantRegistrationRewardAction;
use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
use Throwable;

final class GrantRegistrationRewardListener
{
    public function __construct(
        private readonly GrantRegistrationRewardAction $grantRegistrationReward,
    ) {}

    public function handle(UserRegisteredEvent $event): void
    {
        try {
            $user = User::query()->find($event->userId);

            if (! $user) {
                return;
            }

            $redemption = $this->grantRegistrationReward->execute($user, $event->occurredAt);

            if (! $redemption?->wasRecentlyCreated || ! request()->hasSession()) {
                return;
            }

            $fulfillment = $redemption->fulfillment;

            request()->session()->flash('loyalty_registration_gift', [
                'title' => $redemption->rewardTitle(),
                'type' => $redemption->rewardSnapshot()['type'] ?? null,
                'coupon_code' => $fulfillment instanceof Coupon ? $fulfillment->code : null,
            ]);
        } catch (Throwable $exception) {
            // Registration has already committed; a gift failure must never make
            // the account creation look unsuccessful to the new user.
            report($exception);
        }
    }
}
