<?php

namespace App\Application\Seo\DTOs;

final readonly class SeoPaginationLinksDTO
{
    public function __construct(
        public ?string $prev = null,
        public ?string $next = null,
    )
    {
    }

    public function hasAny(): bool
    {
        return filled($this->prev) || filled($this->next);
    }
}
