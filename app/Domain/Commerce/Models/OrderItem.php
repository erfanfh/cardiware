<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Marketplace\Models\Store;
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
        'product_id',
        'store_id',
        'quantity',
        'unit_price',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'coupon_discount_amount',
        'final_total_amount',
        'seller_commission_basis_points',
        'platform_commission_basis_points',
        'seller_commission_amount',
        'platform_commission_amount',
        'commission_snapshotted_at',
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
        'coupon_discount_amount' => 'integer',
        'final_total_amount' => 'integer',
        'seller_commission_basis_points' => 'integer',
        'platform_commission_basis_points' => 'integer',
        'seller_commission_amount' => 'integer',
        'platform_commission_amount' => 'integer',
        'commission_snapshotted_at' => 'datetime',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sellerCommissionAmount(): int
    {
        return (int) ($this->seller_commission_amount ?? 0);
    }

    public function platformCommissionAmount(): int
    {
        // Legacy paid items have no snapshot and belong entirely to the platform.
        return (int) ($this->platform_commission_amount ?? $this->final_total_amount);
    }

    public function sellerCommissionBasisPoints(): int
    {
        return (int) ($this->seller_commission_basis_points ?? 0);
    }

    public function platformCommissionBasisPoints(): int
    {
        return (int) ($this->platform_commission_basis_points ?? 10000);
    }
}
