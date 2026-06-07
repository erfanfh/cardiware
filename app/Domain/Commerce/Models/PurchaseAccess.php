<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'downloads_count',
        'starts_at',
        'expires_at',
        'last_downloaded_at',
        'metadata',
    ];

    protected $casts = [
        'status' => PurchaseAccessStatusEnum::class,
        'download_limit' => 'integer',
        'downloads_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'metadata' => 'array',
    ];

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
     * Determine whether download limit has been reached.
     */
    public function hasReachedDownloadLimit(): bool
    {
        return $this->download_limit !== null
            && $this->downloads_count >= $this->download_limit;
    }

    /**
     * Determine whether this access can be used for download.
     */
    public function isUsable(): bool
    {
        return $this->status->isUsable()
            && !$this->isExpiredByDate()
            && !$this->hasReachedDownloadLimit();
    }
}
