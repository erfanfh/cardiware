<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a selected attribute value for a product sale item.
 */
class ProductSaleItemAttributeValue extends Model
{
    protected $fillable = [
        'product_sale_item_id',
        'product_attribute_value_id',
        'product_custom_attribute_value_id',
    ];

    /**
     * Get the sale item that owns this selected value.
     *
     * @return BelongsTo
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    /**
     * Get the global attribute value.
     *
     * @return BelongsTo
     */
    public function globalValue(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class, 'product_attribute_value_id');
    }

    /**
     * Get the custom attribute value.
     *
     * @return BelongsTo
     */
    public function customValue(): BelongsTo
    {
        return $this->belongsTo(ProductCustomAttributeValue::class, 'product_custom_attribute_value_id');
    }

    /**
     * Determine whether this row points to a global attribute value.
     *
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->product_attribute_value_id !== null;
    }

    /**
     * Determine whether this row points to a custom attribute value.
     *
     * @return bool
     */
    public function isCustom(): bool
    {
        return $this->product_custom_attribute_value_id !== null;
    }
}
