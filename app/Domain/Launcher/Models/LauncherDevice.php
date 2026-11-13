<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\PersonalAccessToken;

class LauncherDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'fingerprint_hash',
        'hardware_fingerprint_hash',
        'public_key',
        'public_key_fingerprint',
        'device_slot',
        'offline_license_expires_at',
        'platform',
        'app_version',
        'last_ip',
        'last_seen_at',
        'last_security_report_at',
        'revoked_at',
    ];

    protected $hidden = ['fingerprint_hash', 'hardware_fingerprint_hash', 'public_key'];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'last_security_report_at' => 'datetime',
        'revoked_at' => 'datetime',
        'device_slot' => 'integer',
        'offline_license_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(PersonalAccessToken::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }
}
