<?php

namespace App\Application\Launcher\Actions;

use App\Application\Commerce\Exceptions\PurchaseAccessDownloadSessionExpiredException;
use App\Application\Commerce\Exceptions\LauncherManifestChangedException;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrepareLauncherContentDownloadAction
{
    public function __construct(
        private readonly RemoteDownloadFileMetadataResolver $metadataResolver,
        private readonly GenerateTemporaryLauncherDownloadUrlAction $generateTemporaryDownloadUrlAction,
    ) {
    }

    /**
     * Chooses between handing back the download host's own URL and a signed URL that
     * proxies through this server (see launcher.direct_download_enabled for why the
     * direct form exists and what it trades away).
     *
     * Direct mode still refuses anything that is not HTTPS on an allow-listed host.
     * Those two checks already run when an administrator saves a file, but they are
     * repeated here because this is the moment the value is handed to a client: a URL
     * stored before the allow-list was tightened, or edited directly in the database,
     * must not slip out just because it passed validation once. Falling back to the
     * proxy on failure keeps the download working rather than breaking it.
     */
    private function resolveDownloadUrl(
        Model $file,
        Model $access,
        Model $content,
        string $contentType,
        int $fileId,
        string $downloadSessionId,
        int $manifestRevision,
        CarbonInterface $signedUrlExpiresAt,
    ): string {
        if ((bool)config('launcher.direct_download_enabled', false)) {
            $directUrl = trim((string)$file->getAttribute('url'));
            if ($this->isDeliverableDirectUrl($directUrl)) {
                return $directUrl;
            }

            Log::warning('launcher.direct_download.url_rejected', [
                'file_id' => $fileId,
                'content_type' => $contentType,
                'host' => (string)parse_url($directUrl, PHP_URL_HOST),
            ]);
        }

        return $this->generateTemporaryDownloadUrlAction->execute(
            purchaseAccessId: $access->getKey(),
            contentType: $contentType,
            contentId: $content->getKey(),
            fileId: $fileId,
            downloadSessionId: $downloadSessionId,
            manifestRevision: $manifestRevision,
            expiresAt: $signedUrlExpiresAt,
        );
    }

    private function isDeliverableDirectUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower((string)($parts['host'] ?? ''));
        if ($host === '') {
            return false;
        }

        $allowed = array_filter(array_map(
            static fn($value): string => strtolower(trim((string)$value)),
            (array)config('launcher.allowed_download_hosts', [])
        ));

        // An empty allow-list means the deployment never configured one. Refusing here
        // is deliberate: silently handing out whatever host happens to be stored would
        // turn a missing config into an open redirect for our own customers.
        if ($allowed === []) {
            return false;
        }

        foreach ($allowed as $candidate) {
            if ($host === $candidate || str_ends_with($host, '.' . $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{download_session_id:string,session_expires_at:string,url:string,file_name:string,size_bytes:int,sha256:string}
     */
    public function execute(
        User $user,
        LauncherDevice $device,
        int $accessId,
        string $contentType,
        int $contentId,
        int $fileId,
        string $sessionId,
        ?int $expectedManifestRevision = null,
        ?string $expectedFileName = null,
        ?int $expectedSizeBytes = null,
        ?string $expectedSha256 = null,
    ): array {
        [$accessSnapshot, $contentSnapshot, $fileSnapshot] = $this->resolveAccessContentAndFile(
            user: $user,
            accessId: $accessId,
            contentType: $contentType,
            contentId: $contentId,
            fileId: $fileId,
        );

        $sourceUrl = (string)$fileSnapshot->url;
        $metadata = $this->metadataResolver->resolve(
            sourceType: $contentType,
            file: $fileSnapshot,
            url: $sourceUrl,
        );

        return DB::transaction(function () use (
            $user,
            $device,
            $accessSnapshot,
            $contentSnapshot,
            $fileSnapshot,
            $sourceUrl,
            $metadata,
            $contentType,
            $contentId,
            $fileId,
            $sessionId,
            $expectedManifestRevision,
            $expectedFileName,
            $expectedSizeBytes,
            $expectedSha256,
        ): array {
            $access = PurchaseAccess::query()
                ->withoutGlobalScope(ActivePurchaseAccessScope::class)
                ->whereKey($accessSnapshot->getKey())
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!$access->isUsableForLauncher()) {
                throw new PurchaseAccessIsNotUsableException();
            }

            [$content, $file] = $this->resolveContentAndFile(
                saleItemId: (int)$access->product_sale_item_id,
                type: $contentType,
                contentId: $contentId,
                fileId: $fileId,
            );

            if (
                $content->getKey() !== $contentSnapshot->getKey()
                || $file->getKey() !== $fileSnapshot->getKey()
                || !hash_equals($sourceUrl, (string)$file->url)
            ) {
                throw new NotFoundHttpException('فایل محتوای لانچر پیدا نشد.');
            }

            $manifestRevision = (int) ProductSaleItem::query()
                ->withTrashed()
                ->whereKey($access->product_sale_item_id)
                ->value('manifest_revision');
            $fileName = $this->resolveFileName($file);
            $this->assertExpectedManifest(
                manifestRevision: $manifestRevision,
                fileName: $fileName,
                metadata: $metadata,
                expectedManifestRevision: $expectedManifestRevision,
                expectedFileName: $expectedFileName,
                expectedSizeBytes: $expectedSizeBytes,
                expectedSha256: $expectedSha256,
            );

            $download = LauncherDownloadSession::query()
                ->where('purchase_access_id', $access->getKey())
                ->where('launcher_device_id', $device->getKey())
                ->where('content_type', $contentType)
                ->where('content_id', $contentId)
                ->where('file_id', $fileId)
                ->where('download_session_id', $sessionId)
                ->first();

            if ($download && $download->invalidated_at !== null) {
                throw new PurchaseAccessDownloadSessionExpiredException();
            }

            if ($download && (
                (int) $download->manifest_revision !== $manifestRevision
                || !hash_equals(strtolower((string) $download->file_sha256), strtolower($metadata['sha256']))
                || (int) $download->file_size_bytes !== $metadata['size_bytes']
                || !hash_equals((string) $download->file_name, $fileName)
            )) {
                $download->forceFill([
                    'invalidated_at' => now(),
                    'session_expires_at' => now(),
                ])->save();
                throw new LauncherManifestChangedException($manifestRevision);
            }

            $reusedSession = $download instanceof LauncherDownloadSession;

            if (!$download) {
                $download = LauncherDownloadSession::query()->create([
                    'purchase_access_id' => $access->getKey(),
                    'launcher_device_id' => $device->getKey(),
                    'content_type' => $contentType,
                    'content_id' => $contentId,
                    'file_id' => $fileId,
                    'download_session_id' => $sessionId,
                    'manifest_revision' => $manifestRevision,
                    'file_sha256' => $metadata['sha256'],
                    'file_size_bytes' => $metadata['size_bytes'],
                    'file_name' => $fileName,
                    'session_expires_at' => $this->newSessionLeaseExpiration(),
                    'last_prepared_at' => now(),
                    'prepare_count' => 1,
                ]);
            } else {
                $download->forceFill([
                    'session_expires_at' => $this->newSessionLeaseExpiration(),
                    'last_prepared_at' => now(),
                    'prepare_count' => max(1, (int)$download->prepare_count) + 1,
                ])->save();
            }

            $signedUrlExpiresAt = now()->addMinutes(
                max(1, (int)config('launcher.download_url_ttl_minutes', 60))
            );
            if ($download->session_expires_at->lessThan($signedUrlExpiresAt)) {
                $signedUrlExpiresAt = $download->session_expires_at;
            }

            return [
                'download_session_id' => (string)$download->download_session_id,
                'session_expires_at' => $download->session_expires_at->toISOString(),
                'url' => $this->resolveDownloadUrl(
                    file: $file,
                    access: $access,
                    content: $content,
                    contentType: $contentType,
                    fileId: $fileId,
                    downloadSessionId: (string)$download->download_session_id,
                    manifestRevision: $manifestRevision,
                    signedUrlExpiresAt: $signedUrlExpiresAt,
                ),
                'file_name' => $fileName,
                'size_bytes' => $metadata['size_bytes'],
                'sha256' => $metadata['sha256'],
                'manifest_revision' => $manifestRevision,
                'reused_session' => $reusedSession,
            ];
        });
    }

    /**
     * @return array{0:PurchaseAccess,1:Model,2:Model}
     */
    private function resolveAccessContentAndFile(
        User $user,
        int $accessId,
        string $contentType,
        int $contentId,
        int $fileId,
    ): array {
        $access = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($accessId)
            ->where('user_id', $user->getKey())
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->firstOrFail();

        if (!$access->isUsableForLauncher()) {
            throw new PurchaseAccessIsNotUsableException();
        }

        [$content, $file] = $this->resolveContentAndFile(
            saleItemId: (int)$access->product_sale_item_id,
            type: $contentType,
            contentId: $contentId,
            fileId: $fileId,
        );

        return [$access, $content, $file];
    }

    /**
     * @return array{0:Model,1:Model}
     */
    private function resolveContentAndFile(
        int $saleItemId,
        string $type,
        int $contentId,
        int $fileId,
    ): array {
        if ($type === 'update') {
            $content = LauncherUpdate::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $saleItemId)
                ->published()
                ->first();

            $file = $content
                ? LauncherUpdateFile::query()
                    ->whereKey($fileId)
                    ->where('launcher_update_id', $content->getKey())
                    ->first()
                : null;
        } elseif ($type === 'mod') {
            $content = LauncherMod::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $saleItemId)
                ->published()
                ->first();

            $file = $content
                ? LauncherModFile::query()
                    ->whereKey($fileId)
                    ->where('launcher_mod_id', $content->getKey())
                    ->first()
                : null;
        } else {
            $content = $file = null;
        }

        if (!$content instanceof Model || !$file instanceof Model) {
            throw new NotFoundHttpException('فایل محتوای لانچر پیدا نشد.');
        }

        return [$content, $file];
    }

    private function resolveFileName(Model $file): string
    {
        $explicit = trim((string)($file->getAttribute('file_name') ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        $path = (string)parse_url((string)$file->getAttribute('url'), PHP_URL_PATH);
        $fromUrl = basename(rawurldecode($path));

        return $fromUrl !== '' && $fromUrl !== '.' && $fromUrl !== '/'
            ? $fromUrl
            : 'download.bin';
    }

    /**
     * @param array{size_bytes:int,sha256:string,mime_type:?string} $metadata
     */
    private function assertExpectedManifest(
        int $manifestRevision,
        string $fileName,
        array $metadata,
        ?int $expectedManifestRevision,
        ?string $expectedFileName,
        ?int $expectedSizeBytes,
        ?string $expectedSha256,
    ): void {
        if (
            ($expectedManifestRevision !== null && $expectedManifestRevision !== $manifestRevision)
            || ($expectedFileName !== null && !hash_equals($expectedFileName, $fileName))
            || ($expectedSizeBytes !== null && $expectedSizeBytes !== $metadata['size_bytes'])
            || ($expectedSha256 !== null
                && !hash_equals(strtolower($expectedSha256), strtolower($metadata['sha256'])))
        ) {
            throw new LauncherManifestChangedException($manifestRevision);
        }
    }

    private function newSessionLeaseExpiration(): CarbonInterface
    {
        return now()->addMinutes(
            max(1, (int)config('launcher.download_session_lease_minutes', 10080))
        );
    }
}
