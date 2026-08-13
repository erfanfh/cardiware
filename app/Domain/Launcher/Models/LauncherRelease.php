<?php

namespace App\Domain\Launcher\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LauncherRelease extends Model
{
    protected $fillable = [
        'platform',
        'channel',
        'version',
        'minimum_supported_version',
        'is_mandatory',
        'is_enabled',
        'download_url',
        'file_name',
        'size_bytes',
        'sha256',
        'release_notes',
        'published_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_enabled' => 'boolean',
        'size_bytes' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_enabled', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
