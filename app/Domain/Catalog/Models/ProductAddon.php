<?php

namespace App\Domain\Catalog\Models;

use App\Application\Commerce\Contracts\Purchasable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property string|null $description
 * @property int $price
 * @property int $max_quantity_per_order
 * @property bool $requires_purchase
 * @property bool $is_active
 * @property int $sort_order
 * @property array|null $metadata
 */
class ProductAddon extends Model implements Purchasable
{
    protected $fillable = [
        'product_id',
        'title',
        'description',
        'price',
        'max_quantity_per_order',
        'requires_purchase',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price' => 'integer',
        'max_quantity_per_order' => 'integer',
        'requires_purchase' => 'boolean',
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
        return $this->requires_purchase;
    }

    /**
     * Get dependency data for dependent purchasable items.
     *
     * @return array<string, mixed>|null
     */
    public function getPurchasableDependency(): ?array
    {
        if (!$this->requires_purchase) {
            return null;
        }

        return [
            'required_parent_type' => 'product_sale_item',
            'product_id' => $this->product_id,
            'allowed_product_sale_item_ids' => $this->productSaleItems()
                ->pluck('product_sale_items.id')
                ->map(fn($id) => (int)$id)
                ->all(),
        ];
    }

    /**
     * Get the maximum quantity allowed per order.
     */
    public function getMaxQuantityPerOrder(): ?int
    {
        return $this->max_quantity_per_order;
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
            'title' => $this->title,
            'description' => $this->description,
            'product_id' => $this->product_id,
            'product_title' => $this->product?->title,
            'product_addon_id' => $this->id,
            'product_addon_title' => $this->title,
        ];
    }
}
