<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a sellable item of a product.
 */
class ProductSaleItem extends Model
{
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
}
