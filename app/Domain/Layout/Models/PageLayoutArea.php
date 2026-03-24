<?php

namespace App\Domain\Layout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageLayoutArea extends Model
{
    protected $fillable = [
        'page_layout_row_id',
        'key',
        'title',
        'description',
        'width',
        'sort_order',
    ];

    /**
     * @return BelongsTo
     */
    public function row(): BelongsTo
    {
        return $this->belongsTo(PageLayoutRow::class, 'page_layout_row_id');
    }

    /**
     * @return HasMany
     */
    public function elements(): HasMany
    {
        return $this->hasMany(PageLayoutElement::class)->orderBy('sort_order');
    }

    /**
     * @param $query
     * @param string $key
     * @return Builder
     */
    public function scopeByKey($query, string $key): Builder
    {
        return $query->where('key', $key);
    }
}
