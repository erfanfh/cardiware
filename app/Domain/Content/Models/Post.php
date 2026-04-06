<?php

namespace App\Domain\Content\Models;

use App\Domain\Audit\Traits\Auditable;
use App\Domain\Content\Enums\PostMediaCollectionEnum;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Traits\HasComments;
use App\Domain\Download\Models\DownloadSection;
use App\Domain\Feature\Traits\HasFeatures;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Taxonomy\Traits\HasTaxonomyTerms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model implements PageViewableInterface, CommentableContract
{
    use SoftDeletes, HasTaxonomyTerms, Auditable, HasFeatures, HasComments;

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
    ];

    protected $casts = [
        'status' => PostStatusEnum::class,
        'is_pinned' => 'boolean',
    ];

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

    /**
     * @return BelongsTo
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * @param array|string|null $tags
     * @return array
     */
    public function resolveTagIds(array|string|null $tags): array
    {
        return collect($tags)->map(function ($tagName) {
            return TaxonomyTerm::firstOrCreate(
                ['title' => $tagName, 'type' => TaxonomyTermTypeEnum::TAG->value],
//                ['slug' => (new GenerateUniqueSlugAction())->execute($tagName, TaxonomyTermTypeEnum::TAG)]
            )->id;
        })->all();
    }

    /**
     * @return bool
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === PostStatusEnum::ACTIVE;
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return url($this->slug ?? '/');
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
}
