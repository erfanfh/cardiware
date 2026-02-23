<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'nosnippet',
        'max_snippet',
        'max_image_preview',
        'max_video_preview',
        'og_title',
        'og_description',
        'og_image_url',
        'schema_json',
        'focus_keyword',
        'secondary_keywords',
        'seo_score',
        'readability_score',
        'last_analyzed_at',
    ];
}
