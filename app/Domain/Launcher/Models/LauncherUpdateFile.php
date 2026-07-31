<?php

namespace App\Domain\Launcher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LauncherUpdateFile extends Model
{
    protected $fillable = [
        'launcher_update_id', 'title', 'file_name', 'url', 'size_bytes',
        'sha256', 'mime_type', 'part_number', 'is_required', 'sort_order',
    ];

    protected $hidden = ['url'];

    protected $casts = [
        'size_bytes' => 'integer',
        'part_number' => 'integer',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function update(): BelongsTo
    {
        return $this->belongsTo(LauncherUpdate::class, 'launcher_update_id');
    }
}
