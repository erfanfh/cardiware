<?php

namespace App\Domain\Icon\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Icon extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'icon_set_id',
        'name',
        'full_name',
        'search_text',
        'svg',
        'import_batch',
    ];

    public function set(): BelongsTo
    {
        return $this->belongsTo(IconSet::class, 'icon_set_id');
    }

    public function scopeFromSet(Builder $query, ?string $setKey): Builder
    {
        return $query->when(
            filled($setKey),
            fn(Builder $builder) => $builder->whereHas(
                'set',
                fn(Builder $setQuery) => $setQuery->where('key', $setKey)
            )
        );
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string)$search);

        if ($search === '') {
            return $query;
        }

        $normalized = str($search)
            ->lower()
            ->replace([':', '-', '_'], ' ')
            ->squish()
            ->toString();

        return $query->where(function (Builder $builder) use ($search, $normalized) {
            $builder
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('search_text', 'like', "%{$normalized}%");
        });
    }
}
