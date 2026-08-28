<?php

namespace App\Application\Loyalty\PointConversion\DTOs;

use App\Domain\Identity\Models\User;

final readonly class ConvertPointsToWalletDTO
{
    public function __construct(
        public User $user,
        public int $points,
        public string $idempotencyKey,
    ) {}
}
