<?php

namespace App\Domain\Layout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageLayoutRow extends Model
{
    protected $fillable = [
        'page_layout_id',
        'key',
        'layout_type',
        'sort_order',
    ];

    /**
     * @return BelongsTo
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(PageLayout::class, 'page_layout_id');
    }

    /**
     * @return HasMany
     */
    public function areas(): HasMany
    {
        return $this->hasMany(PageLayoutArea::class)->orderBy('sort_order');
    }

    /**
     * @param $query
     * @param string $layoutType
     * @return Builder
     */
    public function scopeByLayoutType($query, string $layoutType): Builder
    {
        return $query->where('layout_type', $layoutType);
    }
}
