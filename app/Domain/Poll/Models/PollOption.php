<?php

namespace App\Domain\Poll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PollOption extends Model
{
    protected $fillable = ['label', 'description', 'color', 'sort_order', 'is_active'];

    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean', 'responses_count' => 'integer'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function responses(): BelongsToMany
    {
        return $this->belongsToMany(PollResponse::class, 'poll_response_options')->withTimestamps();
    }
}
