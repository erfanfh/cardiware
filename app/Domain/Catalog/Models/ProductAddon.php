<?php

namespace App\Domain\Catalog\Models;

use App\Application\Commerce\Contracts\Purchasable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductAddon extends Model implements Purchasable
{
    protected $fillable = [
        'product_id',
        'title',
        'description',
        'price',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the product that owns the add-on.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get sale items that this add-on is limited to.
     */
    public function productSaleItems(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductSaleItem::class,
            'product_addon_product_sale_item',
            'product_addon_id',
            'product_sale_item_id'
        )->withTimestamps();
    }

    /**
     * Get the purchasable display title.
     */
    public function getPurchasableTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the current price in toman.
     */
    public function getPurchasablePrice(): int
    {
        return (int)$this->price;
    }

    /**
     * Determine whether this item can be purchased.
     */
    public function isPurchasable(): bool
    {
        return $this->is_active;
    }

    /**
     * Determine whether this item is dependent on another purchasable item.
     */
    public function isDependentPurchasable(): bool
    {
        return true;
    }

    /**
     * Get dependency data for dependent purchasable items.
     *
     * @return array<string, mixed>|null
     */
    public function getPurchasableDependency(): ?array
    {
        return [
            'required_parent_type' => 'product_sale_item',
            'product_id' => $this->product_id,
        ];
    }

    /**
     * Get the maximum quantity allowed per order.
     */
    public function getMaxQuantityPerOrder(): ?int
    {
        return 1;
    }

    /**
     * Get snapshot data for cart/order display.
     *
     * @return array<string, mixed>
     */
    public function getPurchasableSnapshot(): array
    {
        return [
            'type' => 'product_addon',
            'product_id' => $this->product_id,
            'addon_id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'unit_price' => $this->price,
        ];
    }

    public function isPurchasableAvailable(): bool
    {
        return $this->is_active;
    }
}
