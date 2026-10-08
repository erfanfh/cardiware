<?php

namespace App\Domain\Career\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplication extends Model
{
    protected $fillable = [
        'career_position_id', 'position_title', 'full_name', 'phone', 'email',
        'preferred_cooperation_type', 'portfolio_url', 'skills_and_experience',
        'resume_path', 'resume_original_name', 'resume_mime', 'status',
        'internal_notes', 'reviewed_by', 'reviewed_at',
        'viewed_by', 'viewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'viewed_at' => 'datetime'];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(CareerPosition::class, 'career_position_id')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }
}
