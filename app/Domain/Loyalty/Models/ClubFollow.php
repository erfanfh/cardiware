<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** دنبال‌کردن یک عضو باشگاه توسط یک کاربر (هر جفت فقط یک‌بار). */
class ClubFollow extends Model
{
    protected $fillable = [
        'follower_id',
        'target_id',
    ];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
