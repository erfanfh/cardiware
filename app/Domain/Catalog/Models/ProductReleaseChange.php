<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReleaseChange extends Model
{
    protected $fillable = [
        'product_release_id',
        'title',
        'sort_order',
        'is_special',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_special' => 'boolean',
    ];

    public function release(): BelongsTo
    {
        return $this->belongsTo(ProductRelease::class, 'product_release_id');
    }
}
