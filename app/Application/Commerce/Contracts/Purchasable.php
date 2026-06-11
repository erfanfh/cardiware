<?php

namespace App\Application\Commerce\Contracts;

interface Purchasable
{
    /**
     * Get the purchasable display title.
     */
    public function getPurchasableTitle(): string;

    /**
     * Get the current price in toman.
     */
    public function getPurchasablePrice(): int;

    /**
     * Determine whether this item can be purchased.
     */
    public function isPurchasable(): bool;

    /**
     * Determine whether this item is dependent on another purchasable item.
     */
    public function isDependentPurchasable(): bool;

    /**
     * Get dependency data for dependent purchasable items.
     *
     * @return array<string, mixed>|null
     */
    public function getPurchasableDependency(): ?array;

    /**
     * Get the maximum quantity allowed per order.
     */
    public function getMaxQuantityPerOrder(): ?int;

    /**
     * Get snapshot data for cart/order display.
     *
     * @return array<string, mixed>
     */
    public function getPurchasableSnapshot(): array;

    public function isPurchasableAvailable(): bool;
}
