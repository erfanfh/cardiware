<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSlug extends Model
{
    protected $fillable = [
        'project_id',
        'route_key',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
