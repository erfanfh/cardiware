<?php

namespace App\Application\Support\Actions;

use App\Application\Media\Services\ImageSniffer;
use App\Application\Media\Services\ImageVariantGenerator;
use App\Application\Support\Exceptions\TicketAttachmentUploadException;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketAttachmentVariantEnum;
use App\Domain\Support\Models\TicketMessage;
use App\Domain\Support\Models\TicketMessageAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class StoreTicketMessageAttachmentsAction
{
    private const STORAGE_PREFIX = 'support/ticket-images';

    public function __construct(
        private readonly ImageSniffer $imageSniffer,
        private readonly ImageVariantGenerator $variantGenerator,
    ) {}

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, TicketMessageAttachment>
     *
     * @throws TicketAttachmentUploadException
     */
    public function execute(TicketMessage $message, array $files, User $uploader): array
    {
        $storedAttachments = [];
        $storedPaths = [];
        $storage = Storage::disk('local');

        try {
            foreach (array_values($files) as $index => $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    throw new RuntimeException('Invalid uploaded ticket image.');
                }

                $extension = strtolower($file->getClientOriginalExtension());

                if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    throw new RuntimeException('Invalid ticket image extension.');
                }

                $binary = file_get_contents($file->getRealPath());

                if (! is_string($binary) || $binary === '') {
                    throw new RuntimeException('Unable to read uploaded ticket image.');
                }

                $metadata = $this->imageSniffer->sniff($binary);
                $this->ensureSafeImage($metadata, $extension);

                $attachment = $message->attachments()->create([
                    'uploaded_by_user_id' => $uploader->getKey(),
                    'original_name' => $this->safeOriginalName($file),
                    'sort_order' => $index,
                ]);
                $storedAttachments[] = $attachment;

                $directory = sprintf(
                    '%s/%s/%s',
                    self::STORAGE_PREFIX,
                    now()->format('Y/m'),
                    Str::uuid(),
                );

                foreach ($this->variantGenerator->generate($binary) as $variantName => $variantData) {
                    $variant = TicketAttachmentVariantEnum::from($variantName);
                    $path = "{$directory}/{$variant->value}.webp";

                    if (! $storage->put($path, $variantData['binary'], ['visibility' => 'private'])) {
                        throw new RuntimeException('Unable to store generated ticket image.');
                    }

                    $storedPaths[] = $path;
                    $attachment->variants()->create([
                        'variant' => $variant,
                        'mime_type' => 'image/webp',
                        'path' => $path,
                        'byte_size' => strlen($variantData['binary']),
                        'width' => (int) $variantData['width'],
                        'height' => (int) $variantData['height'],
                        'checksum' => hash('sha256', $variantData['binary']),
                    ]);
                }

                $attachment->load('variants');
            }
        } catch (Throwable $exception) {
            foreach (array_reverse($storedPaths) as $path) {
                try {
                    $storage->delete($path);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            foreach ($storedAttachments as $attachment) {
                $attachment->variants()->delete();
                $attachment->delete();
            }

            report($exception);

            throw new TicketAttachmentUploadException(
                'بارگذاری تصویر انجام نشد. لطفاً تصویر معتبر دیگری انتخاب کنید.',
                previous: $exception,
            );
        }

        return $storedAttachments;
    }

    /**
     * @param  array{mime: string, width: int, height: int, image_type: int}  $metadata
     */
    private function ensureSafeImage(array $metadata, string $extension): void
    {
        $expectedExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
        ];

        if (
            ! isset($expectedExtensions[$metadata['mime']])
            || ! in_array($extension, $expectedExtensions[$metadata['mime']], true)
        ) {
            throw new RuntimeException('Ticket image extension and content do not match.');
        }

        $maxWidth = (int) config('media.images.max_width', 8000);
        $maxHeight = (int) config('media.images.max_height', 8000);
        $maxPixels = (int) config('media.images.max_pixels', 40_000_000);

        if (
            $metadata['width'] < 1
            || $metadata['height'] < 1
            || $metadata['width'] > $maxWidth
            || $metadata['height'] > $maxHeight
            || ($metadata['width'] * $metadata['height']) > $maxPixels
        ) {
            throw new RuntimeException('Ticket image dimensions are unsafe.');
        }
    }

    private function safeOriginalName(UploadedFile $file): ?string
    {
        $name = basename(trim((string) $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = trim($name);

        return $name === '' ? null : mb_substr($name, 0, 255);
    }
}
