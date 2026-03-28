<?php

namespace App\Application\Taxonomy\Support;

use App\Infrastructure\Support\BulkActions\BulkActionSet;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;

final class CategoryBulkActions
{
    public static function set(): BulkActionSet
    {
        return DefaultBulkActions::set()->only(['delete']);
    }
}
