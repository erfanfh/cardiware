<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\Support\PointLedgerFingerprint;
use App\Application\Loyalty\Support\PointLedgerIdempotencyGuard;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class AwardPointsAction
{
    public function __construct(
        private readonly PointLedgerFingerprint $fingerprint,
        private readonly PointLedgerIdempotencyGuard $idempotencyGuard,
        private readonly ProcessLevelUpAction $processLevelUp,
    ) {
    }

    /** @throws Throwable */
    public function execute(AwardPointsDTO $data): PointLedgerEntry
    {
        $this->validate($data);
        $fingerprint = $this->fingerprint->forAward($data);

        if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
            return $this->loadRelations($existing);
        }

        try {
            return DB::transaction(function () use ($data, $fingerprint): PointLedgerEntry {
                $account = PointAccount::query()
                    ->whereKey($data->account->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
                    return $this->loadRelations($existing);
                }

                $balanceBefore = (int)$account->balance;
                $balanceAfter = $balanceBefore + $data->amount;
                $previousXp = (int)$account->lifetime_xp;

                $account->forceFill([
                    'balance' => $balanceAfter,
                    'lifetime_earned' => (int)$account->lifetime_earned + $data->amount,
                ]);

                if ($data->awardsXp) {
                    $account->lifetime_xp = $previousXp + $data->amount;
                }

                $account->save();

                $entry = PointLedgerEntry::query()->create([
                    'point_account_id' => $account->id,
                    'earning_rule_id' => $data->earningRule?->getKey(),
                    'actor_user_id' => $data->actor?->getKey(),
                    'type' => PointEntryTypeEnum::EARN,
                    'direction' => PointEntryDirectionEnum::CREDIT,
                    'reason' => $data->reason,
                    'amount' => $data->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference_type' => $data->reference?->getMorphClass(),
                    'reference_id' => $data->reference?->getKey(),
                    'idempotency_key' => $data->idempotencyKey,
                    'idempotency_fingerprint' => $fingerprint,
                    'description' => $data->description,
                    'occurred_at' => $data->occurredAt ?? now(),
                    'metadata' => $data->earningRule
                        ? array_merge($data->metadata, [
                            'earning_rule_snapshot' => [
                                'code' => $data->earningRule->code,
                                'type' => $data->earningRule->type->value,
                                'config' => $data->earningRule->config,
                            ],
                        ])
                        : $data->metadata,
                ]);

                // پاداش ارتقای سطح فقط برای امتیازهای XP-دار پردازش می‌شود تا
                // خودِ پاداش سطح، ارتقای بعدی را فعال نکند (زنجیره بی‌پایان).
                if ($data->awardsXp) {
                    $this->processLevelUp->execute($account, $previousXp);
                }

                return $this->loadRelations($entry);
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
                return $this->loadRelations($existing);
            }

            throw $exception;
        }
    }

    private function validate(AwardPointsDTO $data): void
    {
        if (!$data->account->exists) {
            throw new InvalidArgumentException('Point account must exist before points are awarded.');
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException('Awarded points must be greater than zero.');
        }

        $this->validateCommon($data->reason, $data->idempotencyKey, $data->actor, $data->reference);

        if ($data->earningRule && !$data->earningRule->exists) {
            throw new InvalidArgumentException('Earning rule must exist before it can be referenced.');
        }
    }

    private function validateCommon(string $reason, string $idempotencyKey, mixed $actor, mixed $reference): void
    {
        if (trim($reason) === '' || Str::length($reason) > 80) {
            throw new InvalidArgumentException('Point ledger reason is required and cannot exceed 80 characters.');
        }

        if (trim($idempotencyKey) === '' || Str::length($idempotencyKey) > 160) {
            throw new InvalidArgumentException('Point ledger idempotency key is required and cannot exceed 160 characters.');
        }

        if ($actor && !$actor->exists) {
            throw new InvalidArgumentException('Point ledger actor must exist.');
        }

        if ($reference && !$reference->exists) {
            throw new InvalidArgumentException('Point ledger reference must exist.');
        }
    }

    private function loadRelations(PointLedgerEntry $entry): PointLedgerEntry
    {
        return $entry->loadMissing(['account', 'earningRule', 'actor', 'reference']);
    }
}
