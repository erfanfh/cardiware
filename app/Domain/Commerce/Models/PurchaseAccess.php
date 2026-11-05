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
    /**
     * دسترسی امانیِ اشتراک ویژه برای محصولات لانچری: مالکیت نیست و فقط تا
     * وقتی اشتراک VIP کاربر فعال است معتبر است.
     */
    public const SOURCE_VIP_BORROW = 'vip_borrow';

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

    /** فقط دسترسی‌های مالکیتی (خرید/دریافت رایگان)، بدون دسترسی امانی VIP. */
    public function scopeOwned(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('metadata->source')
                ->orWhere('metadata->source', '!=', self::SOURCE_VIP_BORROW);
        });
    }

    public function scopeVipBorrow(Builder $query): Builder
    {
        return $query->where('metadata->source', self::SOURCE_VIP_BORROW);
    }

    public function isVipBorrow(): bool
    {
        return data_get($this->metadata, 'source') === self::SOURCE_VIP_BORROW;
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
        // دسترسی امانی VIP در پرس‌وجوها «مالکیت» حساب نمی‌شود؛ اعتبارش فقط
        // در isUsableForLauncher() و به‌صورت زنده از روی اشتراک چک می‌شود.
        return $query
            ->owned()
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
    /**
     * مثل launcherUsable، به‌علاوه‌ی دسترسی‌های امانی VIP وقتی کاربر اشتراک
     * فعال دارد و همان نسخه را خودش مالک نیست (برای لیست کتابخانه لانچر).
     */
    public function scopeLauncherUsableFor(Builder $query, User $user): Builder
    {
        $hasBorrow = static::query()
            ->withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->vipBorrow()
            ->exists();

        if (!$hasBorrow || !$user->hasActiveVipSubscription()) {
            return $query->launcherUsable();
        }

        return $query->where(function (Builder $query) use ($user): void {
            $query
                ->where(fn(Builder $owned): Builder => $owned->launcherUsable())
                ->orWhere(fn(Builder $borrow): Builder => $borrow
                    ->vipBorrow()
                    ->active()
                    ->whereHas('productSaleItem', fn(Builder $saleItem): Builder => $saleItem
                        ->where('launcher_enabled', true)
                        ->where('vip_access_enabled', true))
                    ->whereNotExists(function ($owned) use ($user): void {
                        $table = (new static())->getTable();
                        $owned->selectRaw('1')
                            ->from($table.' as owned_access')
                            ->whereColumn('owned_access.product_sale_item_id', $table.'.product_sale_item_id')
                            ->where('owned_access.user_id', $user->getKey())
                            ->where('owned_access.status', PurchaseAccessStatusEnum::ACTIVE->value)
                            ->where(function ($source): void {
                                $source->whereNull('owned_access.metadata->source')
                                    ->orWhere('owned_access.metadata->source', '!=', self::SOURCE_VIP_BORROW);
                            });
                    }));
        });
    }

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

        // دسترسی امانیِ اشتراک ویژه: فقط تا وقتی اشتراک VIP فعال است معتبر
        // است؛ با پایان اشتراک، محصول دوباره قفل می‌شود.
        if ($this->isVipBorrow()) {
            // اگر ادمین دسترسی VIP یا لانچر این نسخه را خاموش کند، دسترسی
            // امانی هم فوراً از کار می‌افتد.
            $saleItem = $this->relationLoaded('productSaleItem') ? $this->productSaleItem : null;
            $attributes = $saleItem?->getAttributes() ?? [];
            if (!array_key_exists('vip_access_enabled', $attributes) || !array_key_exists('launcher_enabled', $attributes)) {
                $saleItem = ProductSaleItem::query()
                    ->withoutGlobalScopes()
                    ->whereKey($this->product_sale_item_id)
                    ->first(['id', 'vip_access_enabled', 'launcher_enabled']);
            }

            if (!$saleItem || !$saleItem->vip_access_enabled || !$saleItem->launcher_enabled) {
                return false;
            }

            $user = $this->relationLoaded('user')
                ? $this->user
                : $this->user()->first();

            return $user?->hasActiveVipSubscription() ?? false;
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

    /**
     * دسترسی امانی VIP را برای محصولِ دارای vip_access_enabled می‌سازد یا
     * برمی‌گرداند. id خروجی همان چیزی است که لانچر برای library/mods استفاده
     * می‌کند؛ اعتبارش همیشه زنده از روی اشتراک فعال چک می‌شود.
     */
    public static function ensureVipBorrowAccess(User $user, ProductSaleItem $saleItem): self
    {
        /** @var self|null $existing */
        $existing = static::query()
            ->withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->where('product_sale_item_id', $saleItem->getKey())
            ->vipBorrow()
            ->latest('id')
            ->first();

        if ($existing instanceof self) {
            $existing->setRelation('user', $user);
            $existing->forceFill([
                'status' => PurchaseAccessStatusEnum::ACTIVE,
                'expires_at' => null,
            ])->save();

            return $existing;
        }

        $created = static::query()->create([
            'user_id' => $user->getKey(),
            'product_id' => $saleItem->product_id,
            'product_sale_item_id' => $saleItem->getKey(),
            'status' => PurchaseAccessStatusEnum::ACTIVE,
            'download_limit' => null,
            'starts_at' => now(),
            'expires_at' => null,
            'metadata' => [
                'source' => self::SOURCE_VIP_BORROW,
                'created_reason' => 'vip_subscription_unlock',
            ],
        ]);

        return $created->setRelation('user', $user);
    }
}
