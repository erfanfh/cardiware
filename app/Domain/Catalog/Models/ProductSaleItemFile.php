<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a downloadable file link for a product sale item.
 */
class ProductSaleItemFile extends Model
{
    protected $fillable = [
        'product_sale_item_id',
        'title',
        'url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the sale item that owns the file.
     *
     * @return BelongsTo
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }
}
