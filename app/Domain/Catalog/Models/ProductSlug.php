<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSlug extends Model
{
    protected $fillable = [
        'product_id',
        'route_key',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
