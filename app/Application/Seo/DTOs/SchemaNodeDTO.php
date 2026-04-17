<?php

namespace App\Application\Seo\DTOs;

readonly class SchemaNodeDTO
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array $data,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
