<?php

namespace App\Domain\Catalog\Models;

use App\Application\Commerce\Contracts\ProductReportablePurchasable;
use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a sellable item of a product.
 */
class ProductSaleItem extends Model implements Purchasable, ProductReportablePurchasable
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'base_price',
        'status',
        'download_limit',
        'download_expires_in_days',
        'max_quantity_per_order',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'integer',
        'download_limit' => 'integer',
        'download_expires_in_days' => 'integer',
        'max_quantity_per_order' => 'integer',
        'sort_order' => 'integer',
        'status' => ProductSaleItemStatusEnum::class,
    ];

    /**
     * Get the product that owns the sale item.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the downloadable files of the sale item.
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProductSaleItemFile::class)
            ->orderBy('sort_order');
    }

    /**
     * Get the selected attribute values of the sale item.
     *
     * @return HasMany
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductSaleItemAttributeValue::class);
    }

    /**
     * Get the purchasable display title.
     */
    public function getPurchasableTitle(): string
    {
        return $this->product?->title ?? 'آیتم فروش محصول';
    }

    /**
     * Get the current price in toman.
     */
    public function getPurchasablePrice(): int
    {
        return (int)$this->base_price;
    }

    /**
     * Determine whether this item can be purchased.
     */
    public function isPurchasable(): bool
    {
        return $this->status === ProductSaleItemStatusEnum::AVAILABLE;
    }

    /**
     * Determine whether this item is dependent on another purchasable item.
     */
    public function isDependentPurchasable(): bool
    {
        return false;
    }

    /**
     * Get dependency data for dependent purchasable items.
     *
     * @return array<string, mixed>|null
     */
    public function getPurchasableDependency(): ?array
    {
        return null;
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
            'type' => 'product_sale_item',
            'title' => $this->getPurchasableTitle(),
            'product_id' => $this->product_id,
            'store_id' => $this->product?->store_id,
            'image_url' => $this->product?->picture?->url,
            'product_title' => $this->product?->title,
            'product_sale_item_id' => $this->id,
            'product_sale_item_title' => $this->title ?? null,
            'sku' => $this->sku,
            'max_quantity_per_order' => $this->max_quantity_per_order,
        ];
    }

    public function isPurchasableAvailable(): bool
    {
        if ($this->status !== ProductSaleItemStatusEnum::AVAILABLE) {
            return false;
        }

        $product = $this->product()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->first();

        if (!$product) {
            return false;
        }

        if (method_exists($product, 'trashed') && $product->trashed()) {
            return false;
        }

        return $product->status === ProductStatusEnum::ACTIVE;
    }

    /**
     * Get purchase accesses for this sale item.
     */
    public function purchaseAccesses(): HasMany
    {
        return $this->hasMany(PurchaseAccess::class);
    }

    public function getReportProductId(): ?int
    {
        return $this->product_id;
    }

    public function getReportStoreId(): ?int
    {
        return $this->product?->store_id;
    }

    public function discount(): HasOne
    {
        return $this->hasOne(ProductSaleItemDiscount::class);
    }
}
