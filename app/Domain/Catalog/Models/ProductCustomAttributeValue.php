<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a value of a product-scoped custom attribute.
 */
class ProductCustomAttributeValue extends Model
{
    protected $fillable = [
        'product_custom_attribute_id',
        'title',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the custom attribute that owns the value.
     *
     * @return BelongsTo
     */
    public function customAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductCustomAttribute::class, 'product_custom_attribute_id');
    }
}
