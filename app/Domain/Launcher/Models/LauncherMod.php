<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Enums\LauncherModInstallTargetEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LauncherMod extends Model
{
    protected $fillable = [
        'product_sale_item_id', 'launcher_mod_group_id', 'name', 'slug',
        'short_description', 'description', 'version', 'archive_password', 'install_target', 'image_media_id',
        'size_bytes', 'priority', 'is_enabled', 'is_required',
        'compatible_base_versions', 'compatible_update_versions', 'published_at',
    ];

    protected $casts = [
        'install_target' => LauncherModInstallTargetEnum::class,
        'archive_password' => 'encrypted',
        'size_bytes' => 'integer',
        'priority' => 'integer',
        'is_enabled' => 'boolean',
        'is_required' => 'boolean',
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_enabled', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
