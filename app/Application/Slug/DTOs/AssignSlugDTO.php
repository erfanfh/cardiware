<?php

namespace App\Application\Slug\DTOs;

use App\Domain\Slug\Enums\SlugScopeEnum;
use Illuminate\Database\Eloquent\Model;

class AssignSlugDTO
{
    public function __construct(
        public readonly SlugScopeEnum $scope,
        public readonly Model         $model,
        public readonly string        $desiredSlug,
    )
    {
    }
}
