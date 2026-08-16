<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Commerce\Exceptions\PurchaseAccessDownloadLimitExceededException;
use App\Application\Commerce\Exceptions\PurchaseAccessDownloadSessionExpiredException;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Application\Launcher\Actions\GenerateTemporaryLauncherDownloadUrlAction;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class PreparePurchaseAccessFileDownloadAction
{
    public function __construct(
        private readonly HasPurchaseAccessFileReachedDownloadLimitAction $hasReachedDownloadLimitAction,
        private readonly RemoteDownloadFileMetadataResolver $metadataResolver,
        private readonly GenerateTemporaryLauncherDownloadUrlAction $generateTemporaryDownloadUrlAction,
    ) {
    }

    /**
     * @return array{download_session_id:string,session_expires_at:string,url:string,file_name:string,size_bytes:int,sha256:string}
     * @throws Throwable
     */
    public function execute(
        User $user,
        int $purchaseAccessId,
        int $fileId,
        string $downloadSessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        /*
         * Resolve and verify the real file outside the DB transaction. A large
         * archive may take time to hash on its first verification and must not
         * keep the purchase_access row locked while network I/O is happening.
         */
        [$accessSnapshot, $fileSnapshot] = $this->resolveAccessAndFile(
            user: $user,
            purchaseAccessId: $purchaseAccessId,
            fileId: $fileId,
        );

        $sourceUrl = (string)$fileSnapshot->url;
        $metadata = $this->metadataResolver->resolve(
            sourceType: 'product',
            file: $fileSnapshot,
            url: $sourceUrl,
        );

        return DB::transaction(function () use (
            $user,
            $accessSnapshot,
            $fileSnapshot,
            $sourceUrl,
            $metadata,
            $downloadSessionId,
            $ipAddress,
            $userAgent,
        ): array {
            $purchaseAccess = PurchaseAccess::query()
                ->withoutGlobalScope(ActivePurchaseAccessScope::class)
                ->whereKey($accessSnapshot->getKey())
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!$purchaseAccess->isUsableForLauncher()) {
                throw new PurchaseAccessIsNotUsableException();
            }

            $file = ProductSaleItemFile::query()
                ->whereKey($fileSnapshot->getKey())
                ->where('product_sale_item_id', $purchaseAccess->product_sale_item_id)
                ->where('is_active', true)
                ->first();

            if (!$file || !hash_equals($sourceUrl, (string)$file->url)) {
                throw new NotFoundHttpException('فایل موردنظر برای این دسترسی پیدا نشد.');
            }

            $existingDownload = PurchaseAccessDownload::query()
                ->where('purchase_access_id', $purchaseAccess->getKey())
                ->where('product_sale_item_file_id', $file->getKey())
                ->where('download_session_id', $downloadSessionId)
                ->first();

            if ($existingDownload instanceof PurchaseAccessDownload) {
                return $this->reuseExistingSession(
                    purchaseAccess: $purchaseAccess,
                    file: $file,
                    download: $existingDownload,
                    metadata: $metadata,
                );
            }

            return $this->createNewSession(
                user: $user,
                purchaseAccess: $purchaseAccess,
                file: $file,
                downloadSessionId: $downloadSessionId,
                metadata: $metadata,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );
        });
    }

    /**
     * @return array{0:PurchaseAccess,1:ProductSaleItemFile}
     */
    private function resolveAccessAndFile(
        User $user,
        int $purchaseAccessId,
        int $fileId,
    ): array {
        $purchaseAccess = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($purchaseAccessId)
            ->where('user_id', $user->getKey())
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->firstOrFail();

        if (!$purchaseAccess->isUsableForLauncher()) {
            throw new PurchaseAccessIsNotUsableException();
        }

        $file = ProductSaleItemFile::query()
            ->whereKey($fileId)
            ->where('product_sale_item_id', $purchaseAccess->product_sale_item_id)
            ->where('is_active', true)
            ->first();

        if (!$file instanceof ProductSaleItemFile) {
            throw new NotFoundHttpException('فایل موردنظر برای این دسترسی پیدا نشد.');
        }

        return [$purchaseAccess, $file];
    }

    /**
     * @param array{size_bytes:int,sha256:string,mime_type:?string} $metadata
     * @return array{download_session_id:string,session_expires_at:string,url:string,file_name:string,size_bytes:int,sha256:string}
     */
    private function createNewSession(
        User $user,
        PurchaseAccess $purchaseAccess,
        ProductSaleItemFile $file,
        string $downloadSessionId,
        array $metadata,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $remainingBeforeDownload = $this->hasReachedDownloadLimitAction->remaining(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        if ($remainingBeforeDownload !== null && $remainingBeforeDownload <= 0) {
            throw new PurchaseAccessDownloadLimitExceededException();
        }

        $sessionExpiresAt = now()->addMinutes(
            max(1, (int)config('launcher.download_session_ttl_minutes', 1440))
        );
        $fileName = $this->resolveFileName($file);

        $download = PurchaseAccessDownload::query()->create([
            'purchase_access_id' => $purchaseAccess->getKey(),
            'user_id' => $user->getKey(),
            'product_id' => $purchaseAccess->product_id,
            'product_sale_item_id' => $purchaseAccess->product_sale_item_id,
            'product_sale_item_file_id' => $file->getKey(),
            'download_session_id' => $downloadSessionId,
            'session_expires_at' => $sessionExpiresAt,
            'last_prepared_at' => now(),
            'prepare_count' => 1,
            'file_name' => $fileName,
            'file_disk' => 'remote_url',
            'file_path' => (string)$file->url,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? Str::limit($userAgent, 1000, '') : null,
            'metadata' => [
                'source' => 'windows_launcher',
                'file_title' => $file->title,
                'size_bytes' => $metadata['size_bytes'],
                'sha256' => $metadata['sha256'],
            ],
        ]);

        $purchaseAccess->forceFill([
            'last_downloaded_at' => now(),
        ])->save();

        return $this->responseData(
            purchaseAccess: $purchaseAccess,
            file: $file,
            download: $download,
            metadata: $metadata,
        );
    }

    /**
     * @param array{size_bytes:int,sha256:string,mime_type:?string} $metadata
     * @return array{download_session_id:string,session_expires_at:string,url:string,file_name:string,size_bytes:int,sha256:string}
     */
    private function reuseExistingSession(
        PurchaseAccess $purchaseAccess,
        ProductSaleItemFile $file,
        PurchaseAccessDownload $download,
        array $metadata,
    ): array {
        if ($download->session_expires_at === null || $download->session_expires_at->isPast()) {
            throw new PurchaseAccessDownloadSessionExpiredException();
        }

        $download->forceFill([
            'last_prepared_at' => now(),
            'prepare_count' => max(1, (int)$download->prepare_count) + 1,
        ])->save();

        return $this->responseData(
            purchaseAccess: $purchaseAccess,
            file: $file,
            download: $download,
            metadata: $metadata,
        );
    }

    /**
     * @param array{size_bytes:int,sha256:string,mime_type:?string} $metadata
     * @return array{download_session_id:string,session_expires_at:string,url:string,file_name:string,size_bytes:int,sha256:string}
     */
    private function responseData(
        PurchaseAccess $purchaseAccess,
        ProductSaleItemFile $file,
        PurchaseAccessDownload $download,
        array $metadata,
    ): array {
        $expiresAt = $download->session_expires_at;

        if ($expiresAt === null) {
            throw new PurchaseAccessDownloadSessionExpiredException();
        }

        return [
            'download_session_id' => (string)$download->download_session_id,
            'session_expires_at' => $expiresAt->toISOString(),
            'url' => $this->generateTemporaryDownloadUrlAction->execute(
                purchaseAccessId: $purchaseAccess->getKey(),
                contentType: 'product',
                contentId: (int)$purchaseAccess->product_sale_item_id,
                fileId: $file->getKey(),
                downloadSessionId: (string)$download->download_session_id,
                expiresAt: $expiresAt,
            ),
            'file_name' => $download->file_name ?: $this->resolveFileName($file),
            'size_bytes' => $metadata['size_bytes'],
            'sha256' => $metadata['sha256'],
        ];
    }

    private function resolveFileName(ProductSaleItemFile $file): string
    {
        $explicit = trim((string)$file->file_name);

        if ($explicit !== '') {
            return $explicit;
        }

        $path = (string)parse_url((string)$file->url, PHP_URL_PATH);
        $fileName = basename(rawurldecode($path));

        if ($fileName !== '' && $fileName !== '/' && $fileName !== '.') {
            return $fileName;
        }

        $title = trim((string)$file->title);

        return $title !== '' ? $title : "download-{$file->getKey()}";
    }
}
