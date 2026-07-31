<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductMedal extends Model
{
    protected $fillable = [
        'title',
        'picture_id',
        'is_active',
    ];

    protected $casts = [
        'picture_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'picture_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProductMedalAssignment::class, 'product_medal_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_medal_assignments',
            'product_medal_id',
            'product_id'
        )
            ->using(ProductMedalAssignment::class)
            ->withPivot([
                'id',
                'sort_order',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderByPivot('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
