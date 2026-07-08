<?php

namespace App\Infrastructure\Support\BulkActions;

class TicketBulkActions
{
    public static function set(): BulkActionSet
    {
        return BulkActionSet::make([
            'close' => new BulkActionDefinition(
                value: 'close',
                label: 'بستن تیکت‌ها',
            ),

            'reopen' => new BulkActionDefinition(
                value: 'reopen',
                label: 'باز کردن مجدد تیکت‌ها',
            ),
        ]);
    }
}
