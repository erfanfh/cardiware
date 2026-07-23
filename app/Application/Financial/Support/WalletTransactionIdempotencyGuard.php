<?php

namespace App\Application\Financial\Support;

use App\Application\Financial\Exceptions\WalletIdempotencyConflictException;
use App\Domain\Financial\Models\WalletTransaction;

class WalletTransactionIdempotencyGuard
{
    /**
     * Find an already completed operation.
     *
     * If the same key was used for a different financial intent,
     * an exception must be thrown.
     */
    public function find(
        string $idempotencyKey,
        string $fingerprint,
    ): ?WalletTransaction
    {
        $transaction = WalletTransaction::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$transaction) {
            return null;
        }

        $existingFingerprint =
            (string)$transaction->idempotency_fingerprint;

        if (
            blank($existingFingerprint)
            || !hash_equals(
                $existingFingerprint,
                $fingerprint,
            )
        ) {
            throw new WalletIdempotencyConflictException(
                'The wallet idempotency key has already been used for another operation.'
            );
        }

        return $transaction;
    }
}
