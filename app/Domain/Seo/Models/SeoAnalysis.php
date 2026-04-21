<?php

namespace App\Domain\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoAnalysis extends Model
{
    protected $fillable = [
        'score',
        'max_score',
        'problems_count',
        'improvements_count',
        'good_count',
        'results',
        'analyzed_at',
    ];

    protected $casts = [
        'results' => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
