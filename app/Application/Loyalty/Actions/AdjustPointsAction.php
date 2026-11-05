<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\AdjustPointsDTO;
use App\Application\Loyalty\Support\PointLedgerFingerprint;
use App\Application\Loyalty\Support\PointLedgerIdempotencyGuard;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class AdjustPointsAction
{
    public function __construct(
        private readonly PointLedgerFingerprint $fingerprint,
        private readonly PointLedgerIdempotencyGuard $idempotencyGuard,
    ) {
    }

    /** @throws Throwable */
    public function execute(AdjustPointsDTO $data): PointLedgerEntry
    {
        $this->validate($data);
        $fingerprint = $this->fingerprint->forAdjustment($data);

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
                $balanceAfter = $data->direction->isCredit()
                    ? $balanceBefore + $data->amount
                    : $balanceBefore - $data->amount;

                $changes = ['balance' => $balanceAfter];
                $previousXp = (int)$account->lifetime_xp;
                if ($data->affectsXp) {
                    $changes['lifetime_xp'] = $data->direction->isCredit()
                        ? $previousXp + $data->amount
                        : max(0, $previousXp - $data->amount);
                }

                $account->forceFill($changes)->save();

                $entry = PointLedgerEntry::query()->create([
                    'point_account_id' => $account->id,
                    'actor_user_id' => $data->actor?->getKey(),
                    'type' => PointEntryTypeEnum::ADJUSTMENT,
                    'direction' => $data->direction,
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
                    'metadata' => array_merge($data->metadata, ['affects_xp' => $data->affectsXp]),
                ]);

                // تغییر دستی XP مثل کسب/برگشت عادی، سطح و پاداش سطح را هم به‌روز می‌کند.
                if ($data->affectsXp) {
                    $data->direction->isCredit()
                        ? App::make(ProcessLevelUpAction::class)->execute($account, $previousXp)
                        : App::make(RevokeLevelRewardsAboveXpAction::class)->execute($account);
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

    private function validate(AdjustPointsDTO $data): void
    {
        if (!$data->account->exists) {
            throw new InvalidArgumentException('Point account must exist before it is adjusted.');
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException('Point adjustment amount must be greater than zero.');
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
