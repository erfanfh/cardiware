<?php

namespace App\Domain\Project\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProjectSlug extends Model
{
    protected $fillable = [
        'project_id',
        'slug',
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
