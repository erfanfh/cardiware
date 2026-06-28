<?php

namespace App\Application\Commerce\DTOs;

final readonly class PurchasablePriceDTO
{
    public function __construct(
        public int     $basePrice,
        public int     $finalPrice,
        public int     $unitDiscountAmount = 0,
        public ?string $discountSourceType = null,
        public ?int    $discountSourceId = null,
        public ?string $discountTitle = null,
    )
    {
    }

    public function hasDiscount(): bool
    {
        return $this->unitDiscountAmount > 0;
    }

    public function subtotalFor(int $quantity): int
    {
        return $this->basePrice * $quantity;
    }

    public function discountFor(int $quantity): int
    {
        return $this->unitDiscountAmount * $quantity;
    }

    public function totalFor(int $quantity): int
    {
        return $this->finalPrice * $quantity;
    }

    public function toSnapshot(): array
    {
        return [
            'base_price' => $this->basePrice,
            'final_price' => $this->finalPrice,
            'unit_discount_amount' => $this->unitDiscountAmount,
            'has_discount' => $this->hasDiscount(),
            'discount_source_type' => $this->discountSourceType,
            'discount_source_id' => $this->discountSourceId,
            'discount_title' => $this->discountTitle,
        ];
    }
}
