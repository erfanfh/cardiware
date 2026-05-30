<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttributeAssignment extends Model
{
    protected $fillable = [
        'product_id',
        'product_attribute_id',
        'is_visible',
        'is_variation_defining',
        'sort_order',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    /**
     * @return HasMany
     */
    public function selectedValues(): HasMany
    {
        return $this->hasMany(ProductAttributeAssignmentValue::class);
    }
}
