<?php

namespace App\Domain\Security\Models;

use App\Domain\Security\Enums\AbuseEventTypeEnum;
use Illuminate\Database\Eloquent\Model;

class AbuseEvent extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'ip_address',
        'meta',
    ];

    protected $casts = [
        'type' => AbuseEventTypeEnum::class,
    ];
}
