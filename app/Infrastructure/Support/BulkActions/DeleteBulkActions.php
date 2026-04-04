<?php

namespace App\Infrastructure\Support\BulkActions;

final class DeleteBulkActions
{
    public static function set(): BulkActionSet
    {
        return DefaultBulkActions::set()->only(['delete']);
    }
}
