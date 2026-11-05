<?php

namespace App\Application\Launcher\Actions;

use App\Application\Commerce\Exceptions\LauncherManifestChangedException;
use App\Application\Commerce\Exceptions\PurchaseAccessDownloadSessionExpiredException;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherModGrant;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrepareLauncherContentDownloadAction
{
    public function __construct(
        private readonly RemoteDownloadFileMetadataResolver         $metadataResolver,
        private readonly GenerateTemporaryLauncherDownloadUrlAction $generateTemporaryDownloadUrlAction,
        private readonly SignDirectLauncherDownloadUrlAction        $signDirectDownloadUrlAction,
    )
    {
    }

    /**
     * Direct mode returns the download host URL immediately. Otherwise, the signed
     * session URL revalidates access before redirecting to the same download host.
     * Neither path transfers the file through the application server.
     */
    private function resolveDownloadUrl(
        Model           $file,
        Model           $access,
        Model           $content,
        string          $contentType,
        int             $fileId,
        string          $downloadSessionId,
        int             $manifestRevision,
        CarbonInterface $signedUrlExpiresAt,
        User            $user,
    ): string
    {
        if ((bool)config('launcher.direct_download_enabled', false)) {
            $directUrl = trim((string)$file->getAttribute('url'));
            if ($this->isDeliverableDirectUrl($directUrl)) {
                return $this->signDirectDownloadUrlAction->execute($directUrl, $user);
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
        User           $user,
        LauncherDevice $device,
        int            $accessId,
        string         $contentType,
        int            $contentId,
        int            $fileId,
        string         $sessionId,
        ?int           $expectedManifestRevision = null,
        ?string        $expectedFileName = null,
        ?int           $expectedSizeBytes = null,
        ?string        $expectedSha256 = null,
    ): array
    {
        [$accessSnapshot, $contentSnapshot, $fileSnapshot] = $this->resolveAccessContentAndFile(
            user: $user,
            accessId: $accessId,
            contentType: $contentType,
            contentId: $contentId,
            fileId: $fileId,
            expectedFileName: $expectedFileName,
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
                expectedFileName: $expectedFileName,
                userId: (int) $user->getKey(),
            );

            if (
                $content->getKey() !== $contentSnapshot->getKey()
                || $file->getKey() !== $fileSnapshot->getKey()
                || !hash_equals($sourceUrl, (string)$file->url)
            ) {
                throw new NotFoundHttpException('فایل محتوای لانچر پیدا نشد.');
            }

            $manifestRevision = (int)ProductSaleItem::query()
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

            if ($download) {
                $fileIdentityChanged =
                    !hash_equals(strtolower((string)$download->file_sha256), strtolower($metadata['sha256']))
                    || (int)$download->file_size_bytes !== $metadata['size_bytes']
                    || !hash_equals((string)$download->file_name, $fileName);

                if ($fileIdentityChanged) {
                    $download->forceFill([
                        'invalidated_at' => now(),
                        'session_expires_at' => now(),
                    ])->save();
                    throw new LauncherManifestChangedException($manifestRevision);
                }

                // The owning sale item's manifest_revision moved (any qualifying
                // admin edit -- title, an install-size estimate, an unrelated
                // file, a newly published update -- bumps it; see the *Observer
                // classes that call BumpLauncherManifestRevisionAction) but THIS
                // file's sha256/size/name above are byte-for-byte identical. That
                // is not a content change, so the session simply follows the new
                // revision. Previously this was folded into the branch above and
                // forced a full wipe-and-restart-from-zero for a file that never
                // changed.
                if ((int)$download->manifest_revision !== $manifestRevision) {
                    $download->forceFill(['manifest_revision' => $manifestRevision]);
                }
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
                    user: $user,
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
        User    $user,
        int     $accessId,
        string  $contentType,
        int     $contentId,
        int     $fileId,
        ?string $expectedFileName = null,
    ): array
    {
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
            expectedFileName: $expectedFileName,
            userId: (int) $user->getKey(),
        );

        return [$access, $content, $file];
    }

    /**
     * @return array{0:Model,1:Model}
     */
    private function resolveContentAndFile(
        int     $saleItemId,
        string  $type,
        int     $contentId,
        int     $fileId,
        int     $userId,
        ?string $expectedFileName = null,
    ): array
    {
        if ($type === 'update') {
            $saleItem = ProductSaleItem::query()->findOrFail($saleItemId);
            $content = LauncherUpdate::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $saleItemId)
                ->visibleToLauncherUser($userId, $saleItem->staging_user_ids ?? [])
                ->published()
                ->first();

            $file = $content
                ? $this->resolveContentFile(
                    LauncherUpdateFile::query()->where('launcher_update_id', $content->getKey()),
                    $fileId,
                    $expectedFileName,
                )
                : null;
        } elseif ($type === 'mod') {
            $content = LauncherMod::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $saleItemId)
                ->published()
                ->first();

            // مادهای پولی/VIP فقط با گرانت خرید (دائمی) یا اشتراک VIP فعال؛
            // داشتنِ خودِ محصول کافی نیست.
            if ($content instanceof LauncherMod && !$this->userCanAccessMod($content, $userId)) {
                throw new HttpException(403, 'دسترسی به این ماد فعال نیست.');
            }

            $file = $content
                ? $this->resolveContentFile(
                    LauncherModFile::query()->where('launcher_mod_id', $content->getKey()),
                    $fileId,
                    $expectedFileName,
                )
                : null;
        } else {
            $content = $file = null;
        }

        if (!$content instanceof Model || !$file instanceof Model) {
            // TEMPORARY diagnostic for the 2026-09-20 recurring 404 -- remove once root
            // cause is confirmed. warning(), not info(): LOG_LEVEL=warning in production
            // silently drops info().
            Log::warning('launcher.download.content_not_found', [
                'type' => $type,
                'sale_item_id' => $saleItemId,
                'content_id' => $contentId,
                'file_id' => $fileId,
                'content_found' => $content instanceof Model,
                'content_actual_sale_item_id' => $content instanceof Model ? $content->product_sale_item_id : null,
                'content_published_at' => $content instanceof Model ? (string)$content->published_at : null,
                'content_is_enabled' => $content instanceof Model ? (bool)$content->is_enabled : null,
                'file_found' => $file instanceof Model,
            ]);
            throw new NotFoundHttpException('فایل محتوای لانچر پیدا نشد.');
        }

        return [$content, $file];
    }

    /**
     * آیا این کاربر به مادِ پولی دسترسی دارد؟ فقط گرانت (خرید تکی، یا
     * vip_permanent قدیمی) دسترسی می‌دهد؛ اشتراک ویژه ماد باز نمی‌کند.
     */
    private function userCanAccessMod(LauncherMod $mod, int $userId): bool
    {
        if ($mod->isFreeAccess()) {
            return true;
        }

        return LauncherModGrant::query()
            ->where('user_id', $userId)
            ->where('launcher_mod_id', $mod->getKey())
            ->exists();
    }

    /**
     * Picks the row the launcher actually asked for, out of the files belonging to a
     * content record the caller is already proven to own.
     *
     * The file name wins over the id on purpose. Launcher builds up to 1.21.62 put the
     * CONTENT's id in the {file} path segment instead of the file's own id -- invisible
     * for years because early rows were created in lockstep, so launcher_mods.id and
     * launcher_mod_files.id happened to collide. Once those sequences drifted apart the
     * same request started resolving to a sibling row (megaface part 2 asking for id 13
     * and getting part 1) or to nothing at all (update 19 asking for file 19 when its
     * file is 23) -- i.e. the 2026-09-20 "بسته روی سرور تغییر کرده" reset loop and the
     * 404 storm. Scoping is unchanged: the query is already restricted to this content's
     * files, so trusting the client-sent name grants access to nothing new.
     *
     * @param \Illuminate\Database\Eloquent\Builder<covariant Model> $query
     */
    private function resolveContentFile(
        \Illuminate\Database\Eloquent\Builder $query,
        int                                   $fileId,
        ?string                               $expectedFileName,
    ): ?Model
    {
        $files = $query->get();

        $wanted = basename(str_replace('\\', '/', trim((string)$expectedFileName)));
        if ($wanted !== '') {
            $byName = $files->first(
                fn(Model $candidate): bool => basename(
                        str_replace('\\', '/', (string)$candidate->getAttribute('file_name'))
                    ) === $wanted
            );

            if ($byName instanceof Model) {
                return $byName;
            }
        }

        return $files->first(
            fn(Model $candidate): bool => (int)$candidate->getKey() === $fileId
        );
    }

    private function resolveFileName(Model $file): string
    {
        // Must match GetLauncherModsAction::filePayload() / the equivalent update-file
        // payload builder byte-for-byte: the launcher reads file_name from THAT list,
        // sends it back here as expectedFileName, and this action 409s
        // (launcher_manifest_changed) on any difference. Those builders always run the
        // raw column through basename(str_replace('\\','/', ...)); this one used to
        // return the raw column untouched, so any stored value that wasn't already a
        // clean basename (a leading path, a stray backslash) made every download-prepare
        // call fail against the very manifest the launcher had just fetched -- 100%
        // reproducible, not a race, see launcher DOWNLOAD_RESET reason=source_changed
        // spinning at 0 bytes forever on 2026-09-20.
        $explicit = trim((string)($file->getAttribute('file_name') ?? ''));

        if ($explicit !== '') {
            return basename(str_replace('\\', '/', $explicit));
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
        int     $manifestRevision,
        string  $fileName,
        array   $metadata,
        ?int    $expectedManifestRevision,
        ?string $expectedFileName,
        ?int    $expectedSizeBytes,
        ?string $expectedSha256,
    ): void
    {
        $fileIdentityChanged =
            ($expectedFileName !== null && !hash_equals($expectedFileName, $fileName))
            || ($expectedSizeBytes !== null && $expectedSizeBytes !== $metadata['size_bytes'])
            || ($expectedSha256 !== null
                && !hash_equals(strtolower($expectedSha256), strtolower($metadata['sha256'])));

        if ($fileIdentityChanged) {
            Log::warning('launcher.download.manifest_changed', [
                'server_file_name' => $fileName,
                'client_file_name' => $expectedFileName,
                'server_size_bytes' => $metadata['size_bytes'],
                'client_size_bytes' => $expectedSizeBytes,
                'server_sha256' => $metadata['sha256'],
                'client_sha256' => $expectedSha256,
            ]);
            throw new LauncherManifestChangedException($manifestRevision);
        }

        // $expectedManifestRevision is intentionally not compared. The client
        // sends the revision its manifest was fetched with; if the file it is
        // asking for still has the same name/size/sha256, that manifest is
        // accurate for this download regardless of how many unrelated admin
        // edits bumped the revision since (see execute()'s matching branch).
    }

    private function newSessionLeaseExpiration(): CarbonInterface
    {
        return now()->addMinutes(
            max(1, (int)config('launcher.download_session_lease_minutes', 10080))
        );
    }
}
