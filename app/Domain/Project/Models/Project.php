<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'progress_percent',
        'current_version',
        'creator_name',
        'platform',
        'excerpt',
        'body',
        'last_updated_at',
        'released_at',
    ];

    /**
     * @return HasMany
     */
    public function changelogs(): HasMany
    {
        return $this->hasMany(ProjectChangelog::class);
    }

    /**
     * @return HasMany
     */
    public function roadmapItems(): HasMany
    {
        return $this->hasMany(ProjectRoadmapItem::class);
    }
}
