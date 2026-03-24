<?php

namespace App\Domain\Project\Models;

use App\Domain\Project\Enums\RoadmapItemStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRoadmapItem extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'status',
        'progress_percent',
        'excerpt',
        'sort_order',
    ];

    protected $casts = [
        'status' => RoadmapItemStatusEnum::class,
    ];

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
