<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseAccess extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'product_id',
        'product_sale_item_id',
        'status',
        'download_limit',
        'starts_at',
        'expires_at',
        'last_downloaded_at',
        'metadata',
    ];

    protected $casts = [
        'status' => PurchaseAccessStatusEnum::class,
        'download_limit' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ActivePurchaseAccessScope());
    }

    /**
     * Get the user that owns this access.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that granted this access.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item that granted this access.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product sale item.
     */
    public function productSaleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class);
    }

    /**
     * Scope active accesses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PurchaseAccessStatusEnum::ACTIVE->value);
    }

    /**
     * Determine whether access is expired by date.
     */
    public function isExpiredByDate(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Get download logs for this access.
     */
    public function downloads(): HasMany
    {
        return $this->hasMany(PurchaseAccessDownload::class)
            ->latest('id');
    }
}
