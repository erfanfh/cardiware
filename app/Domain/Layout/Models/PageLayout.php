<?php

namespace App\Domain\Layout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageLayout extends Model
{
    protected $fillable = [
        'key',
        'title',
    ];

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    /**
     * @return HasMany
     */
    public function rows(): HasMany
    {
        return $this->hasMany(PageLayoutRow::class)->orderBy('sort_order');
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
