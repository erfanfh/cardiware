<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a product-scoped custom attribute.
 */
class ProductCustomAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'is_visible',
        'is_variation_defining',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_variation_defining' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product that owns the custom attribute.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the values of the custom attribute.
     *
     * @return HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductCustomAttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Get the sale item selections that use this custom value.
     *
     * @return HasMany
     */
    public function saleItemSelections(): HasMany
    {
        return $this->hasMany(ProductSaleItemAttributeValue::class, 'product_custom_attribute_value_id');
    }
}
