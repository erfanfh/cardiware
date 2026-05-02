<?php

namespace App\Domain\Seo\Models;

use App\Domain\Seo\Enums\RedirectStatusCodeEnum;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'source_path',
        'target_url',
        'status_code',
        'format',
        'is_active',
        'hits_count',
        'last_used_at',
    ];

    protected $casts = [
        'status_code' => RedirectStatusCodeEnum::class,
        'is_active' => 'boolean',
        'hits_count' => 'integer',
        'last_used_at' => 'datetime',
    ];
}
