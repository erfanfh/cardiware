<?php

namespace App\Infrastructure\Support\BulkActions;

final class BulkActionResultDTO
{
    public function __construct(
        public readonly string $action,
        public readonly int    $affectedCount,
        public readonly int    $skippedCount = 0,
        public readonly array  $meta = [],
    )
    {
    }
}
