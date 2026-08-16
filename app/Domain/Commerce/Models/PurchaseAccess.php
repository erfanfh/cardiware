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

    /**
     * Scope accesses that are currently usable.
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->active()
            ->where(function (Builder $query) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope accesses that may be exposed to MW Launcher.
     *
     * Historical zero-price claims are accepted only when the administrator
     * explicitly enabled free claiming for their sale item. This prevents an
     * accidentally empty/zero price from granting a paid launcher product.
     */
    public function scopeLauncherUsable(Builder $query): Builder
    {
        return $query
            ->usable()
            ->where(function (Builder $query): void {
                $query
                    ->where(function (Builder $paid): void {
                        $paid
                            ->whereDoesntHave('order')
                            ->orWhereHas('order', function (Builder $order): void {
                                $order
                                    ->whereNull('metadata->source')
                                    ->orWhere('metadata->source', '!=', 'launcher_free_claim');
                            });
                    })
                    ->orWhereHas('productSaleItem', fn (Builder $saleItem): Builder =>
                        $saleItem->where('launcher_free_claim_enabled', true)
                    );
            });
    }

    /**
     * Determine whether this access can currently be used.
     */
    public function isUsable(): bool
    {
        if ($this->status !== PurchaseAccessStatusEnum::ACTIVE) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        return !$this->isExpiredByDate();
    }

    /** Determine whether this access is valid specifically for MW Launcher. */
    public function isUsableForLauncher(): bool
    {
        if (!$this->isUsable()) {
            return false;
        }

        $source = $this->relationLoaded('order')
            ? data_get($this->order?->metadata, 'source')
            : data_get($this->order()->first()?->metadata, 'source');

        if ($source !== 'launcher_free_claim') {
            return true;
        }

        $saleItem = $this->relationLoaded('productSaleItem')
            ? $this->productSaleItem
            : $this->productSaleItem()->first();

        return (bool) $saleItem?->launcher_free_claim_enabled;
    }
}
