<?php

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'expired_at',
        'code',
        'key',
        'key_type',
        'type',
        'is_used',
    ];

    protected $casts = [
        'type' => OtpTypeEnum::class,
        'key_type' => OtpKeyTypeEnum::class,
    ];
}
