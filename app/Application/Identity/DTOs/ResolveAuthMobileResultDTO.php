<?php

namespace App\Application\Identity\DTOs;

final readonly class ResolveAuthMobileResultDTO
{
    public function __construct(
        public string $mobile,
        public bool   $userExists,
    )
    {
    }
}
