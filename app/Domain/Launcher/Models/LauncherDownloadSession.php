<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LauncherDownloadSession extends Model
{
    protected $fillable = [
        'purchase_access_id', 'launcher_device_id', 'content_type', 'content_id',
        'file_id', 'download_session_id', 'session_expires_at',
        'manifest_revision', 'file_sha256', 'file_size_bytes', 'file_name',
        'invalidated_at', 'last_prepared_at', 'prepare_count',
    ];

    protected $casts = [
        'content_id' => 'integer',
        'file_id' => 'integer',
        'manifest_revision' => 'integer',
        'file_size_bytes' => 'integer',
        'session_expires_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'last_prepared_at' => 'datetime',
        'prepare_count' => 'integer',
    ];

    public function access(): BelongsTo
    {
        return $this->belongsTo(PurchaseAccess::class, 'purchase_access_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(LauncherDevice::class, 'launcher_device_id');
    }
}
