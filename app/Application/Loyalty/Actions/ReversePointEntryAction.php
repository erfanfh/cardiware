<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\ReversePointEntryDTO;
use App\Application\Loyalty\Exceptions\PointReversalExceededException;
use App\Application\Loyalty\Support\PointLedgerFingerprint;
use App\Application\Loyalty\Support\PointLedgerIdempotencyGuard;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ReversePointEntryAction
{
    public function __construct(
        private readonly PointLedgerFingerprint $fingerprint,
        private readonly PointLedgerIdempotencyGuard $idempotencyGuard,
    ) {
    }

    /** @throws Throwable */
    public function execute(ReversePointEntryDTO $data): PointLedgerEntry
    {
        $this->validate($data);
        $fingerprint = $this->fingerprint->forReversal($data);

        if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
            return $this->loadRelations($existing);
        }

        try {
            return DB::transaction(function () use ($data, $fingerprint): PointLedgerEntry {
                // Keep the same lock order as award/spend/adjust operations.
                $account = PointAccount::query()
                    ->whereKey($data->entry->point_account_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $original = PointLedgerEntry::query()
                    ->whereKey($data->entry->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int)$original->point_account_id !== (int)$account->id) {
                    throw new InvalidArgumentException(
                        'Point ledger entry does not belong to the expected point account.'
                    );
                }

                if ($existing = $this->idempotencyGuard->find($data->idempotencyKey, $fingerprint)) {
                    return $this->loadRelations($existing);
                }

                $alreadyReversed = (int)PointLedgerEntry::query()
                    ->where('reversal_of_id', $original->id)
                    ->sum('amount');

                if ($alreadyReversed + $data->amount > (int)$original->amount) {
                    throw new PointReversalExceededException(
                        'The requested reversal exceeds the remaining reversible point amount.'
                    );
                }

                $direction = $original->direction->opposite();
                $balanceBefore = (int)$account->balance;
                $balanceAfter = $direction->isCredit()
                    ? $balanceBefore + $data->amount
                    : $balanceBefore - $data->amount;

                $changes = ['balance' => $balanceAfter];

                if ($original->type === PointEntryTypeEnum::EARN) {
                    $changes['lifetime_earned'] = max(
                        0,
                        (int)$account->lifetime_earned - $data->amount,
                    );
                } elseif ($original->type === PointEntryTypeEnum::SPEND) {
                    $changes['lifetime_spent'] = max(
                        0,
                        (int)$account->lifetime_spent - $data->amount,
                    );
                }

                $account->forceFill($changes)->save();

                $entry = PointLedgerEntry::query()->create([
                    'point_account_id' => $account->id,
                    'actor_user_id' => $data->actor?->getKey(),
                    'type' => PointEntryTypeEnum::REVERSAL,
                    'direction' => $direction,
                    'reason' => 'reversal',
                    'amount' => $data->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference_type' => $data->reference?->getMorphClass(),
                    'reference_id' => $data->reference?->getKey(),
                    'reversal_of_id' => $original->id,
                    'idempotency_key' => $data->idempotencyKey,
                    'idempotency_fingerprint' => $fingerprint,
                    'description' => $data->description,
                    'metadata' => array_merge($data->metadata, [
                        'original_entry_type' => $original->type->value,
                        'original_entry_reason' => $original->reason,
                    ]),
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

    private function validate(ReversePointEntryDTO $data): void
    {
        if (!$data->entry->exists) {
            throw new InvalidArgumentException('Point ledger entry must exist before it can be reversed.');
        }

        if ($data->entry->type === PointEntryTypeEnum::REVERSAL) {
            throw new InvalidArgumentException('A reversal entry cannot be reversed directly.');
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException('Reversal amount must be greater than zero.');
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
        return $entry->loadMissing([
            'account',
            'actor',
            'reference',
            'reversalOf',
        ]);
    }
}
