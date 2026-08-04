<?php

namespace App\Application\Discussion\DTOs;

final readonly class CommentReactionIdentityDTO
{
    public function __construct(
        public ?int    $userId,
        public ?string $ipHash,
    )
    {
    }

    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }
}
