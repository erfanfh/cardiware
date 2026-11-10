<?php

namespace App\Domain\Launcher\Models;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Enums\LauncherModAccessTypeEnum;
use App\Domain\Launcher\Enums\LauncherModInstallModeEnum;
use App\Domain\Launcher\Enums\LauncherModInstallTargetEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LauncherMod extends Model implements Purchasable
{
    public const MORPH_ALIAS = 'launcher_mod';

    protected $fillable = [
        'product_sale_item_id', 'launcher_mod_group_id', 'name', 'slug',
        'short_description', 'description', 'version', 'archive_password', 'install_target', 'install_mode', 'image_media_id',
        'size_bytes', 'priority', 'is_enabled', 'is_required',
        'access_type', 'price_toman',
        'warning_text', 'warning_level',
        'compatible_base_versions', 'compatible_update_versions', 'published_at',
    ];

    protected $casts = [
        'install_target' => LauncherModInstallTargetEnum::class,
        'install_mode' => LauncherModInstallModeEnum::class,
        'access_type' => LauncherModAccessTypeEnum::class,
        'archive_password' => 'encrypted',
        'size_bytes' => 'integer',
        'priority' => 'integer',
        'is_enabled' => 'boolean',
        'is_required' => 'boolean',
        'warning_level' => 'string',
        'price_toman' => 'integer',
        'compatible_base_versions' => 'array',
        'compatible_update_versions' => 'array',
        'published_at' => 'datetime',
    ];

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(LauncherModGroup::class, 'launcher_mod_group_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(LauncherModFile::class)->orderBy('sort_order')->orderBy('id');
    }

    public function grants(): HasMany
    {
        return $this->hasMany(LauncherModGrant::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_enabled', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** ماد برای همه‌ی دارندگانِ محصول در دسترس است (رفتار پیش‌فرض و فعلی). */
    public function isFreeAccess(): bool
    {
        return ($this->access_type ?? LauncherModAccessTypeEnum::FREE) === LauncherModAccessTypeEnum::FREE;
    }

    public function isPurchaseAccess(): bool
    {
        return $this->access_type === LauncherModAccessTypeEnum::PURCHASE;
    }

    /**
     * محصولِ مادر؛ سبد خرید و سفارش برای لینک/تصویر/توضیح از ->product
     * استفاده می‌کنند (مثل ProductSaleItem).
     */
    public function getProductAttribute(): ?\App\Domain\Catalog\Models\Product
    {
        return $this->saleItem?->product;
    }

    public function getPurchasableTitle(): string
    {
        $saleItem = $this->saleItem()->first();

        return trim(($saleItem?->title ? $saleItem->title.' — ' : '').$this->name);
    }

    public function getPurchasablePrice(): int
    {
        return max(0, (int) $this->price_toman);
    }

    public function isPurchasable(): bool
    {
        return $this->isPurchasableAvailable();
    }

    public function isPurchasableAvailable(): bool
    {
        return $this->exists
            && $this->isPurchaseAccess()
            && $this->getPurchasablePrice() > 0
            && (bool) $this->is_enabled
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function isDependentPurchasable(): bool
    {
        return false;
    }

    public function getPurchasableDependency(): ?array
    {
        return null;
    }

    public function getMaxQuantityPerOrder(): ?int
    {
        return 1;
    }

    public function getPurchasableSnapshot(): array
    {
        return [
            'type' => 'launcher_mod',
            'title' => $this->getPurchasableTitle(),
            'launcher_mod_id' => $this->getKey(),
            'mod_name' => $this->name,
            'mod_slug' => $this->slug,
            'image_url' => $this->image?->url,
            'price_toman' => $this->getPurchasablePrice(),
            'max_quantity_per_order' => 1,
        ];
    }
}
