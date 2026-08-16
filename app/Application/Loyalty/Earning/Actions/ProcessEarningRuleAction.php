<?php

namespace App\Application\Loyalty\Earning\Actions;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Application\Loyalty\Earning\DTOs\EarningRuleEvaluation;
use App\Application\Loyalty\Earning\DTOs\EarningRuleProcessResult;
use App\Application\Loyalty\Earning\Support\EarningIdempotencyKeyFactory;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;

class ProcessEarningRuleAction
{
    public function __construct(
        private readonly EvaluateEarningRuleAction $evaluateEarningRuleAction,
        private readonly GetOrCreatePointAccountAction $getOrCreatePointAccountAction,
        private readonly AwardPointsAction $awardPointsAction,
        private readonly EarningIdempotencyKeyFactory $idempotencyKeyFactory,
    ) {
    }

    /** @throws Throwable */
    public function execute(
        EarningRuleTypeEnum $type,
        EarningContext $context,
    ): EarningRuleProcessResult {
        $idempotencyKey = $this->idempotencyKeyFactory->make($type, $context);

        if ($existing = $this->findExistingEntry($idempotencyKey)) {
            return new EarningRuleProcessResult(
                EarningRuleEvaluation::fromExisting($existing),
                $existing,
            );
        }

        $account = $this->getOrCreatePointAccountAction->execute($context->user());

        return DB::transaction(function () use (
            $type,
            $context,
            $account,
            $idempotencyKey,
        ): EarningRuleProcessResult {
            /** @var PointAccount $lockedAccount */
            $lockedAccount = PointAccount::query()
                ->whereKey($account->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // Re-check after obtaining the account lock. This closes the race between
            // concurrent requests that carry the same logical earning event.
            if ($existing = $this->findExistingEntry($idempotencyKey)) {
                return new EarningRuleProcessResult(
                    EarningRuleEvaluation::fromExisting($existing),
                    $existing,
                );
            }

            $evaluation = $this->evaluateEarningRuleAction->execute($type, $context);

            if (!$evaluation->shouldAward()) {
                return new EarningRuleProcessResult($evaluation, null);
            }

            if (
                !$evaluation->rule
                || !$evaluation->reason
                || !$evaluation->idempotencyKey
            ) {
                throw new LogicException('Awardable earning rule evaluation is incomplete.');
            }

            if ($evaluation->idempotencyKey !== $idempotencyKey) {
                throw new LogicException('Earning rule idempotency key mismatch.');
            }

            $entry = $this->awardPointsAction->execute(new AwardPointsDTO(
                account: $lockedAccount,
                amount: $evaluation->points,
                reason: $evaluation->reason,
                idempotencyKey: $evaluation->idempotencyKey,
                earningRule: $evaluation->rule,
                reference: $evaluation->reference ?? $context->reference(),
                description: $evaluation->description,
                metadata: array_merge($evaluation->metadata, [
                    'occurred_at' => $context->occurredAt()->toISOString(),
                ]),
                occurredAt: $context->occurredAt(),
            ));

            return new EarningRuleProcessResult($evaluation, $entry);
        }, 3);
    }

    private function findExistingEntry(string $idempotencyKey): ?PointLedgerEntry
    {
        return PointLedgerEntry::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }
}
