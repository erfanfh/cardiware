<?php

namespace App\Domain\System\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BackupRun extends Model
{
    protected $fillable = [
        'type',
        'trigger',
        'status',
        'requested_by_user_id',
        'started_at',
        'finished_at',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
