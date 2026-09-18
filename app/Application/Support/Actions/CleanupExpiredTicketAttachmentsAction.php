<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\TicketMessageAttachment;
use App\Infrastructure\Services\Setting\Groups\TicketSettings;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupExpiredTicketAttachmentsAction
{
    private const CHUNK_SIZE = 100;

    public function __construct(
        private readonly TicketSettings $settings,
        private readonly DeleteTicketMessageAttachmentAction $deleteAttachmentAction,
    ) {}

    public function execute(): int
    {
        $cutoff = now()->subDays($this->settings->attachmentRetentionDays());
        $deletedCount = 0;

        TicketMessageAttachment::query()
            ->where('created_at', '<=', $cutoff)
            ->whereHas('message', function ($query): void {
                $query->where('sender_type', TicketMessageSenderTypeEnum::USER);
            })
            ->with('variants')
            ->chunkById(self::CHUNK_SIZE, function ($attachments) use (&$deletedCount): void {
                foreach ($attachments as $attachment) {
                    try {
                        $this->deleteAttachmentAction->execute($attachment);
                        $deletedCount++;
                    } catch (Throwable $exception) {
                        Log::warning('Failed to delete an expired ticket attachment.', [
                            'attachment_id' => $attachment->getKey(),
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        return $deletedCount;
    }
}
