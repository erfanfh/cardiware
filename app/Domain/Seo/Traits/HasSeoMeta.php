<?php

namespace App\Domain\Seo\Traits;

use App\Domain\Seo\Models\SeoAnalysis;
use App\Domain\Seo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function seoAnalysis(): MorphOne
    {
        return $this->morphOne(SeoAnalysis::class, 'seoable');
    }
}
