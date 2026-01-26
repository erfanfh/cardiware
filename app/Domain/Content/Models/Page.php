<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\StatusEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use Sluggable, SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'template',
        'published_at',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    /**
     * @return MorphMany
     */
    public function slugs(): MorphMany
    {
        return $this->morphMany(ContentSlug::class, 'sluggable');
    }

    /**
     * configs of sluggable package
     *
     * @return array[]
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'unique' => true,
                'includeTrashed' => true
            ]
        ];
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
}
