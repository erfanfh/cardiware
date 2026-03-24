<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFeature extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'subtitle',
        'sort_order',
    ];

    /**
     * Project model
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
