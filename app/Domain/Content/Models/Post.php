<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\PostMediaCollectionEnum;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Scopes\ActivePostScope;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Traits\HasComments;
use App\Domain\Download\Models\DownloadSection;
use App\Domain\Feature\Traits\HasFeatures;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model implements PageViewableInterface, CommentableContract
{
    use SoftDeletes, HasTaxonomyTerms, HasFeatures, HasComments, HasExcerpt, HasSeoMeta;

    protected $hidden = ['edit_lock_token'];

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'is_pinned',
        'picture_id',
        'published_at',
        'created_at',
        'updated_at',
        'type',
        'display_views_offset',
    ];

    protected $casts = [
        'edit_lock_expires_at' => 'datetime',
        'status' => PostStatusEnum::class,
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'updated_at' => 'datetime',
        'display_views_offset' => 'integer',
    ];

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ActivePostScope());
    }

    /**
     * @return MorphMany
     */
    public function slugs(): MorphMany
    {
        return $this->morphMany(ContentSlug::class, 'sluggable');
    }

    /**
     * Get the route key name for Laravel route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /**
     * @return BelongsTo
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * @return bool
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === PostStatusEnum::ACTIVE && (bool)$this->published_at?->lte(now());
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return front_route('contents.detail', $this->slug);
    }

    /**
     * Get the download sections of the post.
     *
     * @return HasMany
     */
    public function downloadSections(): HasMany
    {
        return $this->hasMany(DownloadSection::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return array
     */
    public function getDownloadSectionsForForm(): array
    {
        return $this->downloadSections()
            ->with('entries')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'password' => $section->password,
                    'sort_order' => $section->sort_order,
                    'entries' => $section->entries
                        ->sortBy([
                            ['sort_order', 'asc'],
                            ['id', 'asc'],
                        ])
                        ->values()
                        ->map(function ($entry) {
                            return [
                                'id' => $entry->id,
                                'type' => $entry->type?->value ?? $entry->type,
                                'title' => $entry->title,
                                'content' => $entry->content,
                                'sort_order' => $entry->sort_order,
                                'download_click_count' => $entry->download_click_count,
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class);
    }

    public function imageGallery()
    {
        return $this->media()
            ->where('collection', PostMediaCollectionEnum::IMAGE_GALLERY)
            ->orderBy('sort_order');
    }

    public function videoGallery()
    {
        return $this->media()
            ->where('collection', PostMediaCollectionEnum::VIDEO_GALLERY)
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
     * @return array
     */
    public function getVideoGalleryForForm(): array
    {
        return $this->videoGallery()
            ->with('mediaFile')
            ->get()
            ->map(function (PostMedia $videoGallery) {
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

    /**
     * Post categories relation.
     *
     * @return MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->taxonomyTermsFor(TaxonomyKeyEnum::POST_CATEGORY);
    }

    /**
     * Post tags relation.
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->taxonomyTermsFor(TaxonomyKeyEnum::POST_TAG);
    }

    public function informationBoxes(): HasMany
    {
        return $this->hasMany(PostInformationBox::class);
    }
}
