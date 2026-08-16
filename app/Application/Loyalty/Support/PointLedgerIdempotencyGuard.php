<?php

namespace App\Application\Loyalty\Support;

use App\Application\Loyalty\Exceptions\PointLedgerIdempotencyConflictException;
use App\Domain\Loyalty\Models\PointLedgerEntry;

class PointLedgerIdempotencyGuard
{
    public function find(string $idempotencyKey, string $fingerprint): ?PointLedgerEntry
    {
        $entry = PointLedgerEntry::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (!$entry) {
            return null;
        }

        $existingFingerprint = (string)$entry->idempotency_fingerprint;

        if (
            blank($existingFingerprint)
            || !hash_equals($existingFingerprint, $fingerprint)
        ) {
            throw new PointLedgerIdempotencyConflictException(
                'The point ledger idempotency key has already been used for another operation.'
            );
        }

        return $entry;
    }
}
