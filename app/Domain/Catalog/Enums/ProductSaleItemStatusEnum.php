<?php

namespace App\Domain\Catalog\Enums;

enum ProductSaleItemStatusEnum: string
{
    case AVAILABLE = 'available';
    case UNAVAILABLE = 'unavailable';

    /**
     * Get the human-readable label.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'موجود',
            self::UNAVAILABLE => 'ناموجود',
        };
    }
}
