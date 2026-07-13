<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRelease extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'excerpt',
        'released_at',
        'sort_order',
    ];

    protected $casts = [
        'released_at' => 'date',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function releaseChanges(): HasMany
    {
        return $this->hasMany(ProductReleaseChange::class);
    }
}
