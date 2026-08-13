<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LauncherSlide extends Model
{
    protected $fillable = [
        'title',
        'description',
        'button_text',
        'image_media_id',
        'target_type',
        'target_sale_item_id',
        'target_url',
        'sort_order',
        'is_enabled',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_enabled' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    public function targetSaleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'target_sale_item_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_enabled', true)
            ->where(fn(Builder $query): Builder => $query
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now()))
            ->where(fn(Builder $query): Builder => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>=', now()));
    }
}
