<?php

namespace App\Domain\Poll\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollView extends Model
{
    public $timestamps = false;

    protected $fillable = ['poll_id', 'user_id', 'visitor_hash', 'ip_hash', 'viewed_on', 'viewed_at'];

    protected $casts = ['viewed_on' => 'date', 'viewed_at' => 'datetime'];

    protected $hidden = ['visitor_hash', 'ip_hash'];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
