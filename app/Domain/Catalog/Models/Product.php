<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Discussion\Traits\HasComments;
use App\Domain\Marketplace\Enums\ProductStatusEnum;
use App\Domain\Marketplace\Enums\ProductStructureEnum;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements PageViewableInterface
{
    use SoftDeletes, HasTaxonomyTerms, HasComments, HasSeoMeta, HasExcerpt;

    protected $fillable = [
        'store_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'type',
        'structure',
        'status',
        'picture_id',
        'base_price',
        'currency',
        'published_at',
    ];

    protected $casts = [
        'status' => ProductStatusEnum::class,
        'structure' => ProductStructureEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getUrlAttribute(): string
    {
        return front_route('products.detail', $this->slug);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'picture_id');
    }

    /**
     * Product categories relation.
     *
     * @return MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->taxonomyTermsFor(TaxonomyKeyEnum::PRODUCT_CATEGORY);
    }

    /**
     * Product tags relation.
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->taxonomyTermsFor(TaxonomyKeyEnum::PRODUCT_TAG);
    }
}
