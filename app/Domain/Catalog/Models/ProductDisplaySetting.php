<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDisplaySetting extends Model
{
    protected $fillable = [
        'product_id',
        'show_sales_count',
        'show_average_rating',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
