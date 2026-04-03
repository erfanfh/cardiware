<?php

namespace App\Infrastructure\Support\BulkActions;

final class DefaultBulkActions
{
    public static function set(): BulkActionSet
    {
        return BulkActionSet::make([
            'activate' => new BulkActionDefinition(
                value: 'activate',
                label: 'فعال سازی',
            ),

            'deactivate' => new BulkActionDefinition(
                value: 'deactivate',
                label: 'غیرفعال سازی',
            ),

            'delete' => new BulkActionDefinition(
                value: 'delete',
                label: 'حذف',
                destructive: true,
                requiresConfirmation: true,
            ),
        ]);
    }
}
