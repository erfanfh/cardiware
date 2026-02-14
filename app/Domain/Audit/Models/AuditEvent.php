<?php

namespace App\Domain\Audit\Models;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model
{
    protected $fillable = [
        'type',
        'subject_id',
        'subject_type',
        'user_id',
        'ip_address',
        'user_agent',
        'old_data',
        'new_data',
        'meta',
    ];
    protected $casts = [
        'type' => AuditEventTypeEnum::class
    ];
}
