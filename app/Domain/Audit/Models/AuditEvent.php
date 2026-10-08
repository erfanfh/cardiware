<?php

namespace App\Domain\Audit\Models;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditEvent extends Model
{
    protected $fillable = [
        'type',
        'subject_id',
        'subject_type',
        'subject_label',
        'user_id',
        'actor_name',
        'ip_address',
        'user_agent',
        'old_data',
        'new_data',
        'meta',
    ];

    protected $casts = [
        'type' => AuditEventTypeEnum::class,
        'old_data' => 'array',
        'new_data' => 'array',
        'meta' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
