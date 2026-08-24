<?php

namespace App\Domain\Video\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoSlug extends Model
{
    protected $fillable = ['video_id', 'route_key', 'is_current'];
    protected $casts = ['is_current' => 'boolean'];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
