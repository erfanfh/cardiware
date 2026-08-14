<?php

namespace App\Domain\Launcher\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LauncherNotification extends Model
{
    protected $fillable = [
        'title', 'message', 'type', 'action_text', 'action_url', 'sort_order',
        'is_enabled', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_enabled' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function reads(): HasMany
    {
        return $this->hasMany(LauncherNotificationRead::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_enabled', true)
            ->where(fn(Builder $query): Builder => $query
                ->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn(Builder $query): Builder => $query
                ->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }
}
