<?php

namespace App\Application\Launcher\Support;

use App\Application\Commerce\Exceptions\PurchaseAccessFileUnavailableException;
use App\Domain\Launcher\Models\LauncherDownloadFileIntegrity;
use App\Jobs\VerifyRemoteDownloadFileIntegrityJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RemoteDownloadFileMetadataResolver
{
    /**
     * Resolve integrity metadata for a remote file without ever blocking the
     * caller on a full-file download+hash.
     *
     * A verified-and-cached result (see verifyAndPersist()) is returned
     * immediately. Otherwise, verification of the real remote file — which
     * streams the complete file to compute its SHA-256 and can take minutes
     * for a multi-gigabyte archive — is handed to a background queue job,
     * and the administrator-entered size/checksum already on the file record
     * is returned immediately instead. The launcher independently
     * re-verifies SHA-256 after every download, so this is a safe interim
     * value, not a security-relevant trust decision.
     *
     * @return array{size_bytes:int,sha256:string,mime_type:?string}
     */
    public function resolve(
        string $sourceType,
        Model $file,
        string $url,
    ): array {
        $this->ensureSafeSourceUrl($url);
        $this->ensureConfiguredFileNameMatchesSource($file, $url);

        $urlHash = hash('sha256', $url);

        $integrity = LauncherDownloadFileIntegrity::query()
            ->where('source_type', $sourceType)
            ->where('source_file_id', $file->getKey())
            ->first();

        if ($this->canReuseVerifiedMetadata($integrity, $urlHash)) {
            return $this->metadataFromIntegrity($integrity);
        }

        $this->dispatchBackgroundVerificationOnce($sourceType, $file, $url);

        return $this->metadataFromAdminEnteredValues($file);
    }

    /**
     * Performs the actual remote download+hash and persists the result. This
     * is the slow path: only ever run from the queued job, never inline in a
     * request/response cycle.
     *
     * @return array{size_bytes:int,sha256:string,mime_type:?string}
     */
    public function verifyAndPersist(
        string $sourceType,
        Model $file,
        string $url,
    ): array {
        $urlHash = hash('sha256', $url);
        $metadata = $this->inspectRemoteFile($url);

        $integrity = LauncherDownloadFileIntegrity::query()->updateOrCreate(
            [
                'source_type' => $sourceType,
                'source_file_id' => $file->getKey(),
            ],
            [
                'source_url_hash' => $urlHash,
                'size_bytes' => $metadata['size_bytes'],
                'sha256' => $metadata['sha256'],
                'mime_type' => $metadata['mime_type'],
                'verified_at' => now(),
            ],
        );

        /*
         * Keep the existing file tables in sync as well. Values returned by
         * library/manifests after this verification therefore come from the
         * real file instead of an administrator-entered size/checksum.
         */
        $updates = [
            'size_bytes' => $metadata['size_bytes'],
            'sha256' => $metadata['sha256'],
        ];

        if (array_key_exists('mime_type', $file->getAttributes())) {
            $updates['mime_type'] = $metadata['mime_type'];
        }

        $file->forceFill($updates);

        if ($file->isDirty(array_keys($updates))) {
            /*
             * Do not save quietly here. A real size/SHA change is part of the
             * launcher contract and must bump the owning sale item's
             * manifest_revision through the registered file observer.
             */
            $file->save();
        }

        return $this->metadataFromIntegrity($integrity);
    }

    /**
     * @return array{size_bytes:int,sha256:string,mime_type:?string}
     */
    private function metadataFromAdminEnteredValues(Model $file): array
    {
        return [
            'size_bytes' => (int)$file->getAttribute('size_bytes'),
            'sha256' => strtolower((string)$file->getAttribute('sha256')),
            'mime_type' => array_key_exists('mime_type', $file->getAttributes())
                ? $file->getAttribute('mime_type')
                : null,
        ];
    }

    /**
     * A single stalled/slow file must not queue the same multi-gigabyte
     * verification repeatedly — the launcher's multi-chunk downloader calls
     * this resolver once per chunk per retry for the same file.
     */
    private function dispatchBackgroundVerificationOnce(
        string $sourceType,
        Model $file,
        string $url,
    ): void {
        $lockKey = "launcher-download-integrity-verify:{$sourceType}:{$file->getKey()}";

        if (!Cache::add($lockKey, true, now()->addMinutes(30))) {
            return;
        }

        VerifyRemoteDownloadFileIntegrityJob::dispatch(
            $sourceType,
            $file::class,
            $file->getKey(),
            $url,
            $lockKey,
        );
    }

    public function ensureSafeSourceUrl(string $url): void
    {
        if (
            filter_var($url, FILTER_VALIDATE_URL) === false
            || strtolower((string)parse_url($url, PHP_URL_SCHEME)) !== 'https'
        ) {
            throw new PurchaseAccessFileUnavailableException();
        }

        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $allowedHosts = array_values(array_filter(array_map(
            static fn(string $value): string => strtolower(trim($value)),
            (array)config('launcher.allowed_download_hosts', []),
        )));

        if ($host === '' || ($allowedHosts !== [] && !in_array($host, $allowedHosts, true))) {
            throw new PurchaseAccessFileUnavailableException();
        }
    }

    /**
     * The launcher manifest treats file_name as the physical archive name, not
     * merely as a friendly Content-Disposition alias. Rejecting a mismatch here
     * prevents an existing remote object from silently being saved under a new
     * multipart archive name.
     */
    private function ensureConfiguredFileNameMatchesSource(Model $file, string $url): void
    {
        if (!(bool)config('launcher.require_source_file_name_match', true)) {
            return;
        }

        $configuredName = basename(str_replace(
            '\\',
            '/',
            trim((string)$file->getAttribute('file_name')),
        ));

        if ($configuredName === '') {
            return;
        }

        $sourcePath = rawurldecode((string)parse_url($url, PHP_URL_PATH));
        $sourceName = basename(str_replace('\\', '/', $sourcePath));

        if (
            $sourceName === ''
            || $sourceName === '.'
            || $sourceName === '/'
            || !hash_equals($sourceName, $configuredName)
        ) {
            throw new PurchaseAccessFileUnavailableException();
        }
    }

    private function canReuseVerifiedMetadata(
        ?LauncherDownloadFileIntegrity $integrity,
        string $urlHash,
    ): bool {
        if (!$integrity instanceof LauncherDownloadFileIntegrity) {
            return false;
        }

        if (!hash_equals((string)$integrity->source_url_hash, $urlHash)) {
            return false;
        }

        if (
            (int)$integrity->size_bytes <= 0
            || preg_match('/^[a-f0-9]{64}$/', (string)$integrity->sha256) !== 1
            || $integrity->verified_at === null
        ) {
            return false;
        }

        $ttlMinutes = max(
            0,
            (int)config('launcher.download_integrity_cache_ttl_minutes', 10080)
        );

        return $ttlMinutes > 0
            && $integrity->verified_at->greaterThan(now()->subMinutes($ttlMinutes));
    }

    /**
     * @return array{size_bytes:int,sha256:string,mime_type:?string}
     */
    private function metadataFromIntegrity(
        LauncherDownloadFileIntegrity $integrity,
    ): array {
        return [
            'size_bytes' => (int)$integrity->size_bytes,
            'sha256' => strtolower((string)$integrity->sha256),
            'mime_type' => $integrity->mime_type ?: null,
        ];
    }

    /**
     * @return array{size_bytes:int,sha256:string,mime_type:?string}
     */
    private function inspectRemoteFile(string $url): array
    {
        @set_time_limit(0);

        if (!function_exists('curl_init')) {
            throw new PurchaseAccessFileUnavailableException();
        }

        $hash = hash_init('sha256');
        $sizeBytes = 0;

        $handle = curl_init($url);

        if ($handle === false) {
            throw new PurchaseAccessFileUnavailableException();
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => max(
                1,
                (int)config('launcher.remote_download_connect_timeout_seconds', 15)
            ),
            CURLOPT_TIMEOUT => 0,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/octet-stream,*/*;q=0.8',
                'Accept-Encoding: identity',
            ],
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$sizeBytes, $hash): int {
                $length = strlen($chunk);
                $sizeBytes += $length;
                hash_update($hash, $chunk);

                return $length;
            },
        ]);

        $result = curl_exec($handle);
        $statusCode = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $contentType = trim((string)curl_getinfo($handle, CURLINFO_CONTENT_TYPE));
        $curlError = curl_error($handle);

        curl_close($handle);

        if (
            $result === false
            || $curlError !== ''
            || $statusCode < 200
            || $statusCode >= 300
            || $sizeBytes <= 0
        ) {
            throw new PurchaseAccessFileUnavailableException();
        }

        return [
            'size_bytes' => $sizeBytes,
            'sha256' => strtolower(hash_final($hash)),
            'mime_type' => $contentType !== '' ? $contentType : null,
        ];
    }
}
