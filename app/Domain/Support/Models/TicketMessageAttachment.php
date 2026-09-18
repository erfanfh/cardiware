<?php

namespace App\Domain\Support\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMessageAttachment extends Model
{
    protected $fillable = [
        'ticket_message_id',
        'uploaded_by_user_id',
        'original_name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(TicketMessageAttachmentVariant::class)
            ->orderBy('id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
