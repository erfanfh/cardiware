<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductMediaCollectionEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Discussion\Traits\HasComments;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements PageViewableInterface
{
    use SoftDeletes, HasTaxonomyTerms, HasComments, HasSeoMeta, HasExcerpt;

    protected $fillable = [
        'store_id',
        'title',
        'slug',
        'sku',
        'excerpt',
        'body',
        'type',
        'structure',
        'status',
        'picture_id',
        'published_at',
    ];

    protected $casts = [
        'status' => ProductStatusEnum::class,
        'type' => ProductTypeEnum::class,
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

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function imageGallery(): HasMany
    {
        return $this->media()
            ->where('collection', ProductMediaCollectionEnum::IMAGE_GALLERY)
            ->orderBy('sort_order');
    }

    public function videoGallery(): HasMany
    {
        return $this->media()
            ->where('collection', ProductMediaCollectionEnum::VIDEO_GALLERY)
            ->orderBy('sort_order');
    }

    /**
     * @return array
     */
    public function getImageGalleryForForm(): array
    {
        return $this->imageGallery
            ->map(function ($imageGallery) {
                return [
                    'id' => $imageGallery->id,
                    'title' => $imageGallery->title,
                    'image' => [
                        'id' => $imageGallery->media_file_id,
                        'path' => $imageGallery->mediaFile->url,
                    ],
                    'sort_order' => $imageGallery->sort_order,
                ];
            })
            ->values()
            ->all();
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProductRequirement::class)->orderBy('sort_order');
    }

    public function installationSteps(): HasMany
    {
        return $this->hasMany(ProductInstallationStep::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ProductFaq::class)->orderBy('sort_order');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(ProductRelease::class)->orderBy('sort_order');
    }

    public function attributeAssignments(): HasMany
    {
        return $this->hasMany(ProductAttributeAssignment::class);
    }

    /**
     * Get the sale items of the product.
     *
     * @return HasMany
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(ProductSaleItem::class)->orderBy('sort_order');
    }

    /**
     * Get the custom attributes of the product.
     *
     * @return HasMany
     */
    public function customAttributes(): HasMany
    {
        return $this->hasMany(ProductCustomAttribute::class)->orderBy('sort_order');
    }

    /**
     * Get product add-ons.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(ProductAddon::class)->orderBy('sort_order');
    }
}
