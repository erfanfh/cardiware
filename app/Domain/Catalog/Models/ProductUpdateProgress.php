<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUpdateProgress extends Model
{
    protected $table = 'product_update_progresses';

    protected $fillable = [
        'product_id',
        'is_visible',
        'progress_percentage',
        'started_at',
        'release_at',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'progress_percentage' => 'integer',
        'started_at' => 'date',
        'release_at' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isPublished(): bool
    {
        return $this->progress_percentage >= 100;
    }
}
