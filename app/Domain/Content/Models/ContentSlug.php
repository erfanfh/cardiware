<?php

namespace App\Domain\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentSlug extends Model
{
    protected $fillable = [
        'slug',
        'is_current',
        'sluggable_type',
        'sluggable_id',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * Get the parent sluggable model (Product, Article, etc).
     */
    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }
}
