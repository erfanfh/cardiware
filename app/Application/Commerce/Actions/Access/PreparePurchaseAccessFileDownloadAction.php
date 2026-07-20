<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Commerce\Exceptions\PurchaseAccessDownloadLimitExceededException;
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

class PreparePurchaseAccessFileDownloadAction
{
    public function __construct(
        private readonly HasPurchaseAccessFileReachedDownloadLimitAction $hasReachedDownloadLimitAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(
        User    $user,
        int     $purchaseAccessId,
        int     $fileId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array
    {
        return DB::transaction(function () use (
            $user,
            $purchaseAccessId,
            $fileId,
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

            $remainingBeforeDownload =
                $this->hasReachedDownloadLimitAction->remaining(
                    purchaseAccess: $purchaseAccess,
                    file: $file,
                );

            if (
                $remainingBeforeDownload !== null
                && $remainingBeforeDownload <= 0
            ) {
                throw new PurchaseAccessDownloadLimitExceededException();
            }

            $downloadUrl = app(GeneratePurchaseAccessDownloadUrlAction::class)->execute($purchaseAccess, $file);

            if (
                $downloadUrl === ''
                || filter_var(
                    $downloadUrl,
                    FILTER_VALIDATE_URL
                ) === false
            ) {
                throw new PurchaseAccessFileUnavailableException();
            }

            $scheme = strtolower(
                (string)parse_url(
                    $downloadUrl,
                    PHP_URL_SCHEME
                )
            );

            if (!in_array($scheme, ['http', 'https'], true)) {
                throw new PurchaseAccessFileUnavailableException();
            }

            $this->ensureHostIsAllowed($downloadUrl);

            $fileName = $this->resolveFileName($file);

            $download = PurchaseAccessDownload::query()->create([
                'purchase_access_id' => $purchaseAccess->getKey(),
                'user_id' => $user->getKey(),
                'product_id' => $purchaseAccess->product_id,
                'product_sale_item_id' => $purchaseAccess->product_sale_item_id,
                'product_sale_item_file_id' => $file->getKey(),

                'file_name' => $fileName,
                'file_disk' => 'remote_url',
                'file_path' => parse_url(
                    $downloadUrl,
                    PHP_URL_PATH
                ),

                'ip_address' => $ipAddress,

                'user_agent' => $userAgent !== null
                    ? Str::limit($userAgent, 1000, '')
                    : null,

                'metadata' => [
                    'source' => 'windows_launcher',
                    'file_title' => $file->title,
                    'source_url' => $downloadUrl,
                ],
            ]);

            $downloadedAt = now();

            $lastDownloadedAt = $purchaseAccess->last_downloaded_at;

            $shouldDecreaseDownload = $lastDownloadedAt === null || $lastDownloadedAt->lte($downloadedAt->copy()->subMinutes(15));

            $purchaseAccess->forceFill([
                'last_downloaded_at' => $downloadedAt,
            ])->save();

            return [
                'download_id' => $download->getKey(),
                'url' => $downloadUrl,
                'file_name' => $fileName,
                'title' => $file->title,

                'remaining_downloads' => $remainingBeforeDownload === null
                    ? null
                    : max(
                        0,
                        $remainingBeforeDownload - ($shouldDecreaseDownload ? 1 : 0)
                    ),
            ];
        });
    }

    private function resolveFileName(
        ProductSaleItemFile $file,
    ): string
    {
        $path = (string)parse_url(
            $file->url,
            PHP_URL_PATH
        );

        $fileName = basename($path);

        if ($fileName !== '' && $fileName !== '/') {
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
