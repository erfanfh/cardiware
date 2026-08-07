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
        'last_prepared_at', 'prepare_count',
    ];

    protected $casts = [
        'content_id' => 'integer',
        'file_id' => 'integer',
        'session_expires_at' => 'datetime',
        'last_prepared_at' => 'datetime',
        'prepare_count' => 'integer',
    ];

    public function access(): BelongsTo
    {
        return $this->belongsTo(PurchaseAccess::class, 'purchase_access_id');
    }
}
