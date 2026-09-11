<?php

namespace App\Jobs;

use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Downloads and hashes a remote launcher package file to verify its real
 * size/SHA-256, off the request/response cycle. A multi-gigabyte file can
 * take minutes to stream and must never block a launcher client waiting for
 * a download-prepare response.
 */
class VerifyRemoteDownloadFileIntegrityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CONNECTION = 'redis-download-integrity';

    public const QUEUE = 'download-integrity';

    public int $timeout = 1800;

    public int $tries = 1;

    /**
     * @param class-string<Model> $fileClass
     */
    public function __construct(
        public readonly string $sourceType,
        public readonly string $fileClass,
        public readonly int|string $fileId,
        public readonly string $url,
        public readonly string $lockKey,
    ) {
        $this->onConnection(self::CONNECTION);
        $this->onQueue(self::QUEUE);
    }

    public function handle(RemoteDownloadFileMetadataResolver $resolver): void
    {
        // Jobs already serialized before this queue was split still point at
        // the default queue. Move them without starting the remote download so
        // the existing backlog stops occupying workers used by notifications.
        if ($this->job !== null && $this->job->getQueue() !== self::QUEUE) {
            self::dispatch(
                $this->sourceType,
                $this->fileClass,
                $this->fileId,
                $this->url,
                $this->lockKey,
            );

            return;
        }

        try {
            $file = $this->fileClass::find($this->fileId);
            if (!$file instanceof Model) {
                return;
            }

            $resolver->verifyAndPersist($this->sourceType, $file, $this->url);
        } catch (Throwable $exception) {
            Log::warning('Background launcher download integrity verification failed.', [
                'source_type' => $this->sourceType,
                'file_class' => $this->fileClass,
                'file_id' => $this->fileId,
                'error' => $exception->getMessage(),
            ]);
        } finally {
            Cache::forget($this->lockKey);
        }
    }
}
