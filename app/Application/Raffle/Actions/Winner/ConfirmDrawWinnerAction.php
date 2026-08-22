<?php

namespace App\Application\Raffle\Actions\Winner;

use App\Application\Raffle\Exceptions\DrawWinnerTransitionException;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawWinnerConfirmedEvent;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConfirmDrawWinnerAction
{
    /** @throws Throwable */
    public function execute(DrawWinner $drawWinner, ?User $actor = null, array $claimData = []): DrawWinner
    {
        return DB::transaction(function () use ($drawWinner, $actor, $claimData): DrawWinner {
            $winner = DrawWinner::query()->whereKey($drawWinner->getKey())->lockForUpdate()->firstOrFail();

            if (! $winner->isEffectivePrimary()) {
                throw new DrawWinnerTransitionException('A reserve winner must be promoted before it can be confirmed.');
            }
            if ($winner->status !== DrawWinnerStatusEnum::PENDING) {
                throw new DrawWinnerTransitionException('Only a pending winner can be confirmed.');
            }
            if ($winner->hasReplacement()) {
                throw new DrawWinnerTransitionException('This winner has already been replaced.');
            }

            $now = now()->toImmutable();
            if ($winner->confirmation_deadline_at && $winner->confirmation_deadline_at->lte($now)) {
                throw new DrawWinnerTransitionException('Winner confirmation deadline has already expired.');
            }

            $attributes = [
                'status' => DrawWinnerStatusEnum::CONFIRMED,
                'confirmed_at' => $now,
                'confirmed_by_user_id' => $actor?->getKey(),
            ];

            if ($claimData !== []) {
                $attributes = array_merge($attributes, [
                    'claimed_at' => $now,
                    'claim_contact_name' => $claimData['contact_name'] ?? null,
                    'claim_mobile' => $claimData['mobile'] ?? null,
                    'claim_postal_code' => $claimData['postal_code'] ?? null,
                    'claim_address' => $claimData['address'] ?? null,
                    'claim_ip' => $claimData['ip'] ?? null,
                ]);
            }

            $winner->forceFill($attributes)->save();

            DB::afterCommit(fn () => event(new DrawWinnerConfirmedEvent(
                campaignId: (int) $winner->campaign_id,
                winnerId: (int) $winner->getKey(),
                userId: (int) $winner->user_id,
                actorUserId: $actor?->getKey(),
                occurredAt: $now,
            )));

            return $winner->refresh();
        }, 3);
    }
}
