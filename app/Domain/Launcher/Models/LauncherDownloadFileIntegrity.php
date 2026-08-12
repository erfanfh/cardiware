<?php

namespace App\Domain\Launcher\Models;

use Illuminate\Database\Eloquent\Model;

class LauncherDownloadFileIntegrity extends Model
{
    protected $fillable = [
        'source_type',
        'source_file_id',
        'source_url_hash',
        'size_bytes',
        'sha256',
        'mime_type',
        'verified_at',
    ];

    protected $casts = [
        'source_file_id' => 'integer',
        'size_bytes' => 'integer',
        'verified_at' => 'datetime',
    ];
}
