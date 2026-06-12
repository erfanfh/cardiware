<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSlug extends Model
{
    protected $fillable = [
        'store_id',
        'route_key',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
