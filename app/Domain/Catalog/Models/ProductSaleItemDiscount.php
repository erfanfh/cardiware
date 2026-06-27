<?php

namespace App\Domain\Catalog\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSaleItemDiscount extends Model
{
    protected $fillable = [
        'product_sale_item_id',
        'discounted_price',
        'starts_at',
        'ends_at',
        'show_special_box',
        'special_box_title',
        'special_box_description',
    ];

    protected $casts = [
        'product_sale_item_id' => 'integer',
        'discounted_price' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'show_special_box' => 'boolean',
    ];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    public function isActiveAt(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->starts_at && $this->starts_at->gt($at)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($at)) {
            return false;
        }

        return true;
    }
}
