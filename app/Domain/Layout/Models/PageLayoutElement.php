<?php

namespace App\Domain\Layout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageLayoutElement extends Model
{
    protected $fillable = [
        'page_layout_area_id',
        'key',
        'type',
        'title',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(PageLayoutArea::class, 'page_layout_area_id');
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

    /**
     * @param $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType($query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @return Attribute
     */
    protected function isEnabled(): Attribute
    {
        return Attribute::get(fn() => $this->is_active);
    }
}
