<?php

namespace App\Application\Slug\DTOs;

use App\Domain\Slug\Enums\SlugScopeEnum;

class ResolveSlugDTO
{
    public function __construct(
        public readonly SlugScopeEnum $scope,
        public readonly string        $value,
    )
    {
    }
}
