<?php

namespace App\Domain\Seo\Models;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $fillable = [
        'focus_keyword',
        'title',
        'description',
        'robots',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_id',
        'x_title',
        'x_description',
        'x_image_id',
        'schema_type',
        'schema_settings',
    ];

    protected $casts = [
        'schema_settings' => 'array',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'og_image_id');
    }

    public function xImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'x_image_id');
    }
}
