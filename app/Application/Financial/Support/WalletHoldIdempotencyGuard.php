<?php

namespace App\Application\Financial\Support;

use App\Application\Financial\Exceptions\WalletHoldIdempotencyConflictException;
use App\Domain\Financial\Models\WalletHold;

class WalletHoldIdempotencyGuard
{
    public function find(
        string $idempotencyKey,
        string $fingerprint,
    ): ?WalletHold
    {
        $hold = WalletHold::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$hold) {
            return null;
        }

        $existingFingerprint =
            (string)$hold->idempotency_fingerprint;

        if (
            blank($existingFingerprint)
            || !hash_equals(
                $existingFingerprint,
                $fingerprint,
            )
        ) {
            throw new WalletHoldIdempotencyConflictException(
                'The wallet hold idempotency key has already been used for another operation.'
            );
        }

        return $hold;
    }
}
