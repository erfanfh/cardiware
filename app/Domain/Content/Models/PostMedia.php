<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\PostMediaCollectionEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    protected $fillable = [
        'post_id',
        'media_file_id',
        'collection',
        'sort_order',
        'title',
        'meta',
    ];

    protected $casts = [
        'collection' => PostMediaCollectionEnum::class,
        'meta' => 'array',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }
}
