<?php

namespace App\Domain\Poll\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PollResponse extends Model
{
    protected $fillable = ['poll_id', 'user_id', 'identity_key', 'ip_hash', 'user_agent', 'referrer', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    protected $hidden = ['identity_key', 'ip_hash'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(PollOption::class, 'poll_response_options')->withTimestamps();
    }
}
