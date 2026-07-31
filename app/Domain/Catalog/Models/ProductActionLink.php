<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductActionLink extends Model
{
    protected $fillable = [
        'product_id',
        'title',
        'url',
        'open_in_new_tab',
        'sort_order',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
