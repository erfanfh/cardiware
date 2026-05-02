<?php

namespace App\Domain\Seo\Models;

use App\Domain\Seo\Enums\RedirectStatusCodeEnum;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'source_path',
        'source_path_hash',
        'target_url',
        'status_code',
        'format',
        'is_active',
    ];

    protected $casts = [
        'status_code' => RedirectStatusCodeEnum::class,
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            $redirect->source_path_hash = md5($redirect->source_path);
        });
    }
}
