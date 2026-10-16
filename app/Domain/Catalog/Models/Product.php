<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProductContentTypeEnum;
use App\Domain\Catalog\Enums\ProductMediaCollectionEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Commerce\Enums\PurchaseAccessStatusEnum;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Contracts\HasDiscussionOwner;
use App\Domain\Discussion\Contracts\HasVerifiedDiscussionBuyer;
use App\Domain\Discussion\Enums\CommentAuthorBadge;
use App\Domain\Discussion\Traits\HasComments;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements PageViewableInterface, CommentableContract, HasDiscussionOwner, HasVerifiedDiscussionBuyer
{
    use SoftDeletes, HasTaxonomyTerms, HasComments, HasSeoMeta, HasExcerpt;

    protected $hidden = ['edit_lock_token'];

    protected $fillable = [
        'store_id',
        'seller_commission_basis_points',
        'title',
        'slug',
        'sku',
        'excerpt',
        'body',
        'file_size_body',
        'type',
        'content_type',
        'structure',
        'status',
        'picture_id',
        'published_at',
        'display_views_offset',
    ];

    protected $casts = [
        'edit_lock_expires_at' => 'datetime',
        'status' => ProductStatusEnum::class,
        'type' => ProductTypeEnum::class,
        'content_type' => ProductContentTypeEnum::class,
        'structure' => ProductStructureEnum::class,
        'published_at' => 'datetime',
        'display_views_offset' => 'integer',
        'seller_commission_basis_points' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveProductScope());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getUrlAttribute(): string
    {
        return front_route('products.detail', $this->slug);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === ProductStatusEnum::ACTIVE && (bool)$this->published_at?->lte(now());
    }

    public function hasFileSizeContent(): bool
    {
        $body = $this->file_size_body ?? '';
        $text = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match('/\S/u', $text) === 1
            || preg_match('/<(?:img|video|audio|iframe|embed|object|hr)\b/i', $body) === 1;
    }

    public function displaySetting(): HasOne
    {
        return $this->hasOne(ProductDisplaySetting::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function editLockUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edit_lock_user_id');
    }

    public function isEditLocked(): bool
    {
        return $this->edit_lock_user_id !== null
            && $this->edit_lock_expires_at?->isFuture() === true;
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVideoGalleryForForm(): array
    {
        return $this->videoGallery()
            ->with('mediaFile')
            ->get()
            ->map(function (ProductMedia $videoGallery) {
                return [
                    'id' => $videoGallery->id,
                    'title' => $videoGallery->title,
                    'video_url' => data_get($videoGallery->meta, 'video_url'),
                    'cover' => [
                        'id' => $videoGallery->media_file_id,
                        'path' => $videoGallery->mediaFile?->url,
                    ],
                    'sort_order' => $videoGallery->sort_order,
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

    public function teamMembers(): HasMany
    {
        return $this->hasMany(ProductTeamMember::class)->orderBy('sort_order');
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

    public function informationEntries(): HasMany
    {
        return $this->hasMany(ProductInformationEntry::class);
    }

    public function purchaseAccesses(): HasMany
    {
        return $this->hasMany(PurchaseAccess::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'product_favorites',
            'product_id',
            'user_id'
        )->withTimestamps();
    }

    public function updateProgress(): HasOne
    {
        return $this->hasOne(ProductUpdateProgress::class);
    }

    public function actionLinks(): HasMany
    {
        return $this->hasMany(ProductActionLink::class)->orderBy('sort_order');
    }

    public function medalAssignments(): HasMany
    {
        return $this->hasMany(
            ProductMedalAssignment::class,
            'product_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function medals(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductMedal::class,
            'product_medal_assignments',
            'product_id',
            'product_medal_id'
        )
            ->using(ProductMedalAssignment::class)
            ->withPivot([
                'id',
                'sort_order',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderByPivot('id');
    }

    public function activeMedals(): BelongsToMany
    {
        return $this->medals()->where('product_medals.is_active', true);
    }

    public function commentsEnabled(): bool
    {
        return true;
    }

    public function guestCommentsEnabled(): bool
    {
        return true;
    }

    public function requiresCommentRating(): bool
    {
        return true;
    }

    public function discussionOwnerUserId(): ?int
    {
        return null;
    }

    public function discussionOwnerBadge(): CommentAuthorBadge
    {
        return CommentAuthorBadge::PRODUCT_SELLER;
    }

    public function hasVerifiedDiscussionPurchase(int $userId): bool
    {
        return PurchaseAccess::query()
            ->where('user_id', $userId)
            ->whereIn('status', [
                PurchaseAccessStatusEnum::ACTIVE->value,
                PurchaseAccessStatusEnum::EXPIRED->value,
            ])
            ->whereHas(
                'productSaleItem',
                fn(Builder $query) => $query->where(
                    'product_id',
                    $this->getKey()
                )
            )
            ->exists();
    }
}
