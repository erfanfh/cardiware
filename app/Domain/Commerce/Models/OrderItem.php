<?php

namespace App\Domain\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'parent_order_item_id',
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
        'parent_order_item_id' => 'integer',
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
     * Get the order that owns this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the purchasable model.
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent order item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_order_item_id');
    }

    /**
     * Get child order items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_order_item_id')
            ->orderBy('id');
    }

    /**
     * Determine whether the item is a child item.
     */
    public function isChild(): bool
    {
        return $this->parent_order_item_id !== null;
    }

    /**
     * Determine whether the item is a root item.
     */
    public function isRoot(): bool
    {
        return $this->parent_order_item_id === null;
    }

    /**
     * Get purchase access granted by this order item.
     */
    public function purchaseAccess(): HasOne
    {
        return $this->hasOne(PurchaseAccess::class);
    }
}
