<?php

namespace App\Application\Slug\DTOs;

use Illuminate\Database\Eloquent\Model;

class AssignSlugDTO
{
    public function __construct(
        public readonly string $scope,
        public readonly Model  $model,
        public readonly string $desiredSlug,
    )
    {
    }
}
