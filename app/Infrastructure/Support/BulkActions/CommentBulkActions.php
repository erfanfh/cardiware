<?php

namespace App\Infrastructure\Support\BulkActions;

class CommentBulkActions
{
    public static function set(): BulkActionSet
    {
        return BulkActionSet::make([
            'approve' => new BulkActionDefinition(
                value: 'approve',
                label: 'تایید دیدگاه‌ها',
            ),

            'reject' => new BulkActionDefinition(
                value: 'reject',
                label: 'رد دیدگاه‌ها',
            ),

            'delete' => new BulkActionDefinition(
                value: 'delete',
                label: 'حذف دیدگاه‌ها',
                destructive: true,
                requiresConfirmation: true,
            ),
        ]);
    }
}
