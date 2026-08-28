<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Application\Loyalty\Exceptions\InsufficientPointsException;
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

class SpendPointsAction
{
    public function __construct(
        private readonly PointLedgerFingerprint $fingerprint,
        private readonly PointLedgerIdempotencyGuard $idempotencyGuard,
    ) {
    }

    /** @throws Throwable */
    public function execute(SpendPointsDTO $data): PointLedgerEntry
    {
        $this->validate($data);
        $fingerprint = $this->fingerprint->forSpend($data);

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

                if ((int)$account->balance < $data->amount) {
                    throw new InsufficientPointsException('The point account does not have enough spendable points.');
                }

                $balanceBefore = (int)$account->balance;
                $balanceAfter = $balanceBefore - $data->amount;

                $account->forceFill([
                    'balance' => $balanceAfter,
                    'lifetime_spent' => (int)$account->lifetime_spent + $data->amount,
                ])->save();

                $entry = PointLedgerEntry::query()->create([
                    'point_account_id' => $account->id,
                    'actor_user_id' => $data->actor?->getKey(),
                    'type' => PointEntryTypeEnum::SPEND,
                    'direction' => PointEntryDirectionEnum::DEBIT,
                    'reason' => $data->reason,
                    'amount' => $data->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference_type' => $data->reference?->getMorphClass(),
                    'reference_id' => $data->reference?->getKey(),
                    'idempotency_key' => $data->idempotencyKey,
                    'idempotency_fingerprint' => $fingerprint,
                    'description' => $data->description,
                    'occurred_at' => now(),
                    'metadata' => $data->metadata,
                ]);

                return $this->loadRelations($entry);
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
                return $this->loadRelations($existing);
            }

            throw $exception;
        }
    }

    private function validate(SpendPointsDTO $data): void
    {
        if (!$data->account->exists) {
            throw new InvalidArgumentException('Point account must exist before points are spent.');
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException('Spent points must be greater than zero.');
        }

        if (trim($data->reason) === '' || Str::length($data->reason) > 80) {
            throw new InvalidArgumentException('Point ledger reason is required and cannot exceed 80 characters.');
        }

        if (trim($data->idempotencyKey) === '' || Str::length($data->idempotencyKey) > 160) {
            throw new InvalidArgumentException('Point ledger idempotency key is required and cannot exceed 160 characters.');
        }

        if ($data->actor && !$data->actor->exists) {
            throw new InvalidArgumentException('Point ledger actor must exist.');
        }

        if ($data->reference && !$data->reference->exists) {
            throw new InvalidArgumentException('Point ledger reference must exist.');
        }
    }

    private function loadRelations(PointLedgerEntry $entry): PointLedgerEntry
    {
        return $entry->loadMissing(['account', 'actor', 'reference']);
    }
}
