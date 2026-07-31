<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductMedalAssignment extends Pivot
{
    protected $table = 'product_medal_assignments';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'product_medal_id',
        'sort_order',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'product_medal_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function medal(): BelongsTo
    {
        return $this->belongsTo(
            ProductMedal::class,
            'product_medal_id'
        );
    }
}
