<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Models\TicketMessageAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteTicketMessageAttachmentAction
{
    private const STORAGE_PATH_PATTERN = '#\Asupport/ticket-images/\d{4}/\d{2}/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/(?:original|thumbnail|medium|medium_large|large)\.webp\z#D';

    public function execute(TicketMessageAttachment $attachment): void
    {
        $attachment->loadMissing('variants');
        $storage = Storage::disk('local');

        foreach ($attachment->variants as $variant) {
            if (preg_match(self::STORAGE_PATH_PATTERN, $variant->path) !== 1) {
                throw new RuntimeException('Invalid private ticket image path.');
            }

            if (! $storage->exists($variant->path)) {
                continue;
            }

            if (! $storage->delete($variant->path) && $storage->exists($variant->path)) {
                throw new RuntimeException('Unable to delete a private ticket image.');
            }
        }

        DB::transaction(function () use ($attachment): void {
            $attachment->variants()->delete();
            $attachment->delete();
        });
    }
}
