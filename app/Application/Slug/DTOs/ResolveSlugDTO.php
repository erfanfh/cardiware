<?php

namespace App\Application\Slug\DTOs;

class ResolveSlugDTO
{
    public function __construct(
        public readonly string $scope,
        public readonly string $value,
    )
    {
    }
}
