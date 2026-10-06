<?php

namespace App\Domain\Vip\Models;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VipSaleItemClaim extends Model
{
    protected $fillable = [
        'user_id', 'product_sale_item_id', 'product_title',
        'sale_item_title', 'claimed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }
}
