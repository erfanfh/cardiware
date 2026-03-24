<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectChangelog extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'body',
        'tags',
        'published_at',
        'sort_order',
    ];

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return array
     */
    public function tagsList(): array
    {
        if (blank($this->tags)) {
            return [];
        }

        return collect(preg_split('/[،,]+/u', $this->tags))
            ->map(fn($tag) => trim($tag))
            ->filter(fn($tag) => $tag !== '')
            ->values()
            ->all();
    }
}
