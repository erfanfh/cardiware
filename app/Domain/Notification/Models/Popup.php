<?php

namespace App\Domain\Notification\Models;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Popup extends Model
{
    protected $fillable = [
        'title', 'body', 'image_id', 'display_mode', 'buttons', 'target_type', 'target_id', 'audience',
        'delay_seconds', 'close_minutes', 'hide_days', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'buttons' => 'array',
        'target_id' => 'integer',
        'delay_seconds' => 'integer',
        'close_minutes' => 'integer',
        'hide_days' => 'integer',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_id');
    }
}
