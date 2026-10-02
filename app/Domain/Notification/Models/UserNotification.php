<?php

namespace App\Domain\Notification\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id', 'event_key', 'subject_type', 'subject_id',
        'title', 'body', 'url', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];
}
