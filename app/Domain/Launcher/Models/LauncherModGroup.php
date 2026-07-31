<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Catalog\Models\ProductSaleItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LauncherModGroup extends Model
{
    protected $fillable = ['product_sale_item_id', 'key', 'title', 'is_exclusive', 'sort_order'];

    protected $casts = ['is_exclusive' => 'boolean', 'sort_order' => 'integer'];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    public function mods(): HasMany
    {
        return $this->hasMany(LauncherMod::class)->orderBy('priority')->orderBy('id');
    }
}
