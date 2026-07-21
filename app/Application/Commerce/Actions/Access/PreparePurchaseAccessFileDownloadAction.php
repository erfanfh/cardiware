<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Commerce\Exceptions\PurchaseAccessDownloadLimitExceededException;
use App\Application\Commerce\Exceptions\PurchaseAccessDownloadSessionExpiredException;
use App\Application\Commerce\Exceptions\PurchaseAccessFileUnavailableException;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
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
        private readonly GeneratePurchaseAccessDownloadUrlAction         $generateDownloadUrlAction,
    )
    {
    }

    /**
     * @param User $user
     * @param int $purchaseAccessId
     * @param int $fileId
     * @param string $downloadSessionId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return mixed[]
     * @throws Throwable
     */
    public function execute(
        User    $user,
        int     $purchaseAccessId,
        int     $fileId,
        string  $downloadSessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array
    {
        return DB::transaction(function () use (
            $user,
            $purchaseAccessId,
            $fileId,
            $downloadSessionId,
            $ipAddress,
            $userAgent,
        ): array {
            $purchaseAccess = PurchaseAccess::query()
                ->withoutGlobalScope(ActivePurchaseAccessScope::class)
                ->whereKey($purchaseAccessId)
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (!$purchaseAccess->isUsable()) {
                throw new PurchaseAccessIsNotUsableException();
            }

            $file = ProductSaleItemFile::query()
                ->whereKey($fileId)
                ->where(
                    'product_sale_item_id',
                    $purchaseAccess->product_sale_item_id
                )
                ->first();

            if (!$file) {
                throw new NotFoundHttpException(
                    'فایل موردنظر برای این دسترسی پیدا نشد.'
                );
            }

            /*
             * The purchase access row is locked, so two concurrent
             * requests for the same session cannot both create a
             * download record.
             */
            $existingDownload = PurchaseAccessDownload::query()
                ->where('purchase_access_id', $purchaseAccess->getKey())
                ->where('product_sale_item_file_id', $file->getKey())
                ->where('download_session_id', $downloadSessionId)
                ->first();

            if ($existingDownload) {
                return $this->reuseExistingSession(
                    purchaseAccess: $purchaseAccess,
                    file: $file,
                    download: $existingDownload,
                );
            }

            return $this->createNewSession(
                user: $user,
                purchaseAccess: $purchaseAccess,
                file: $file,
                downloadSessionId: $downloadSessionId,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function createNewSession(
        User                $user,
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
        string              $downloadSessionId,
        ?string             $ipAddress,
        ?string             $userAgent,
    ): array
    {
        $remainingBeforeDownload = $this->hasReachedDownloadLimitAction->remaining(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        if ($remainingBeforeDownload !== null && $remainingBeforeDownload <= 0) {
            throw new PurchaseAccessDownloadLimitExceededException();
        }

        $downloadUrl = $this->prepareDownloadUrl(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        $fileName = $this->resolveFileName($file);

        $sessionExpiresAt = now()->addMinutes(
            max(
                1,
                (int)config(
                    'launcher.download_session_ttl_minutes',
                    1440
                )
            )
        );

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
            'file_path' => parse_url($downloadUrl, PHP_URL_PATH),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? Str::limit($userAgent, 1000, '') : null,
            'metadata' => [
                'source' => 'windows_launcher',
                'file_title' => $file->title,
            ],
        ]);

        /*
         * Only the first request of a new download session
         * changes last_downloaded_at.
         */
        $purchaseAccess->forceFill([
            'last_downloaded_at' => now(),
        ])->save();

        return [
            'download_id' => $download->getKey(),
            'download_session_id' => $download->download_session_id,
            'session_expires_at' => $download->session_expires_at?->toISOString(),
            'reused_session' => false,
            'prepare_count' => 1,
            'url' => $downloadUrl,
            'file_name' => $fileName,
            'title' => $file->title,
            'remaining_downloads' =>
                $remainingBeforeDownload === null
                    ? null
                    : max(0, $remainingBeforeDownload - 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reuseExistingSession(
        PurchaseAccess         $purchaseAccess,
        ProductSaleItemFile    $file,
        PurchaseAccessDownload $download,
    ): array
    {
        if ($download->session_expires_at !== null && $download->session_expires_at->isPast()) {
            throw new PurchaseAccessDownloadSessionExpiredException();
        }

        /*
         * Generate a fresh temporary URL, but do not create another
         * PurchaseAccessDownload record and do not consume quota.
         */
        $downloadUrl = $this->prepareDownloadUrl(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        $download->forceFill([
            'last_prepared_at' => now(),
            'prepare_count' => max(1, (int)$download->prepare_count) + 1,
        ])->save();

        $remainingDownloads = $this->hasReachedDownloadLimitAction->remaining(
            purchaseAccess: $purchaseAccess,
            file: $file,
        );

        return [
            'download_id' => $download->getKey(),
            'download_session_id' => $download->download_session_id,
            'session_expires_at' => $download->session_expires_at?->toISOString(),
            'reused_session' => true,
            'prepare_count' => $download->prepare_count,
            'url' => $downloadUrl,
            'file_name' => $download->file_name ?: $this->resolveFileName($file),
            'title' => $file->title,
            /*
             * The current remaining count is returned without
             * subtracting another download.
             */
            'remaining_downloads' => $remainingDownloads,
        ];
    }

    private function prepareDownloadUrl(
        PurchaseAccess      $purchaseAccess,
        ProductSaleItemFile $file,
    ): string
    {
        $downloadUrl = $this->generateDownloadUrlAction->execute(
            $purchaseAccess,
            $file
        );

        if ($downloadUrl === '' || filter_var($downloadUrl, FILTER_VALIDATE_URL) === false) {
            throw new PurchaseAccessFileUnavailableException();
        }

        $scheme = strtolower((string)parse_url($downloadUrl, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new PurchaseAccessFileUnavailableException();
        }

        $this->ensureHostIsAllowed($downloadUrl);

        return $downloadUrl;
    }

    private function resolveFileName(
        ProductSaleItemFile $file,
    ): string
    {
        $path = (string)parse_url(
            $file->url,
            PHP_URL_PATH
        );

        $fileName = basename(rawurldecode($path));

        if ($fileName !== '' && $fileName !== '/' && $fileName !== '.') {
            return $fileName;
        }

        if (!empty($file->title)) {
            return $file->title;
        }

        return "download-{$file->getKey()}";
    }

    private function ensureHostIsAllowed(string $url): void
    {
        $allowedHosts = config(
            'launcher.allowed_download_hosts',
            []
        );

        if ($allowedHosts === []) {
            return;
        }

        $host = strtolower(
            (string)parse_url($url, PHP_URL_HOST)
        );

        $normalizedAllowedHosts = array_map(
            static fn(string $allowedHost): string => strtolower(
                trim($allowedHost)
            ),
            $allowedHosts
        );

        if (!in_array($host, $normalizedAllowedHosts, true)) {
            throw new PurchaseAccessFileUnavailableException();
        }
    }
}
