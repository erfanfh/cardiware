<?php

namespace App\Domain\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'parent_cart_item_id',
        'purchasable_type',
        'purchasable_id',
        'quantity',
        'unit_price',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'snapshot',
        'metadata',
    ];

    protected $casts = [
        'parent_cart_item_id' => 'integer',
        'purchasable_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'snapshot' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the cart that owns the item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the purchasable model.
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent cart item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_cart_item_id');
    }

    /**
     * Get child cart items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_cart_item_id');
    }

    /**
     * Determine whether the item is a child item.
     */
    public function isChild(): bool
    {
        return $this->parent_cart_item_id !== null;
    }

    /**
     * Determine whether the item is a root item.
     */
    public function isRoot(): bool
    {
        return $this->parent_cart_item_id === null;
    }
}
