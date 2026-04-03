<?php

namespace App\Infrastructure\Support\BulkActions;

final class DeleteBulkActions
{
    public static function set(): BulkActionSet
    {
        return BulkActionSet::make([
            'delete' => new BulkActionDefinition(
                value: 'delete',
                label: 'حذف',
                destructive: true,
                requiresConfirmation: true,
            ),
        ]);
    }
}
