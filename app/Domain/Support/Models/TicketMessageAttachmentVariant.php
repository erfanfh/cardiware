<?php

namespace App\Domain\Support\Models;

use App\Domain\Support\Enums\TicketAttachmentVariantEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessageAttachmentVariant extends Model
{
    protected $fillable = [
        'ticket_message_attachment_id',
        'variant',
        'mime_type',
        'path',
        'byte_size',
        'width',
        'height',
        'checksum',
    ];

    protected $casts = [
        'variant' => TicketAttachmentVariantEnum::class,
        'byte_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(
            TicketMessageAttachment::class,
            'ticket_message_attachment_id',
        );
    }
}
