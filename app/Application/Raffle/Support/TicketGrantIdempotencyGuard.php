<?php

namespace App\Application\Raffle\Support;

use App\Application\Raffle\Exceptions\TicketGrantIdempotencyConflictException;
use App\Domain\Raffle\Models\TicketGrant;

class TicketGrantIdempotencyGuard
{
    public function find(string $key, string $fingerprint): ?TicketGrant
    {
        $grant = TicketGrant::query()
            ->where('idempotency_key', $key)
            ->first();

        if (!$grant) {
            return null;
        }

        $existingFingerprint = (string)$grant->idempotency_fingerprint;

        if (
            blank($existingFingerprint)
            || !hash_equals($existingFingerprint, $fingerprint)
        ) {
            throw new TicketGrantIdempotencyConflictException(
                'The raffle ticket grant idempotency key has already been used for another operation.'
            );
        }

        return $grant;
    }
}
