<?php

namespace App\Application\Discussion\DTOs;

final class CommentReviewDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $authorName,
        public readonly string $body,
        public readonly float $rating,
        public readonly ?string $publishedAt,
    ) {}
}
