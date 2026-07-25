<?php

namespace App\Application\Identity\DTOs;

final readonly class CompleteAuthModalResultDTO
{
    public function __construct(
        public ?string $warningMessage = null,
    )
    {
    }
}
