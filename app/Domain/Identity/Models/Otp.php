<?php

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\OtpTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'expired_at',
        'code',
        'key',
        'type',
        'is_used',
    ];

    protected $casts = [
        'type' => OtpTypeEnum::class,
    ];
}
