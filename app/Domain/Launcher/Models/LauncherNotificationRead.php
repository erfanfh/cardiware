<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LauncherNotificationRead extends Model
{
    public $timestamps = false;

    protected $fillable = ['launcher_notification_id', 'user_id', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(LauncherNotification::class, 'launcher_notification_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
