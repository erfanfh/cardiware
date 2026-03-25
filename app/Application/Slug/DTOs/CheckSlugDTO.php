<?php

namespace App\Application\Slug\DTOs;

final class CheckSlugDTO
{
    public function __construct(
        public readonly string  $scope,
        public readonly string  $slug,
        public readonly ?int    $ignoreId = null,
        public readonly ?string $ignoreType = null,
    )
    {
    }

    /**
     * Create a DTO instance from a plain array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scope: (string)($data['scope'] ?? ''),
            slug: (string)($data['slug'] ?? ''),
            ignoreId: isset($data['ignore']) && $data['ignore'] !== ''
                ? (int)$data['ignore']
                : null,
            ignoreType: isset($data['ignore_type']) && $data['ignore_type'] !== ''
                ? (string)$data['ignore_type']
                : null,
        );
    }
}
