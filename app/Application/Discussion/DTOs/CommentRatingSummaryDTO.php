<?php

namespace App\Application\Discussion\DTOs;

final class CommentRatingSummaryDTO
{
    public function __construct(
        public readonly ?float $averageRating,
        public readonly int $ratingCount,
    ) {}

    public function hasRatings(): bool
    {
        return $this->averageRating !== null && $this->ratingCount > 0;
    }
}
