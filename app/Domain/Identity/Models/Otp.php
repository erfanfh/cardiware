<?php

namespace App\Domain\Identity\Models;

use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'expired_at',
        'resend_available_at',
        'code',
        'code_hash',
        'attempts',
        'key',
        'key_type',
        'type',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'type' => OtpTypeEnum::class,
            'key_type' => OtpKeyTypeEnum::class,
            'expired_at' => 'immutable_datetime',
            'resend_available_at' => 'immutable_datetime',
            'is_used' => 'boolean',
            'attempts' => 'integer',
        ];
    }
}
