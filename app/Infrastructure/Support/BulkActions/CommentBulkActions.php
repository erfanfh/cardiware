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

            'pending' => new BulkActionDefinition(
                value: 'pending',
                label: 'انتقال به انتظار بررسی',
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
