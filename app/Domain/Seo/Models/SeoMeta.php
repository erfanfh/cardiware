<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $fillable = [
        'title',
        'description',
        'robots',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
