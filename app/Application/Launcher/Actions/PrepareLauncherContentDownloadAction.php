<?php

namespace App\Application\Launcher\Actions;

use App\Application\Commerce\Exceptions\PurchaseAccessDownloadSessionExpiredException;
use App\Application\Commerce\Exceptions\PurchaseAccessFileUnavailableException;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrepareLauncherContentDownloadAction
{
    public function execute(
        User $user,
        LauncherDevice $device,
        int $accessId,
        string $contentType,
        int $contentId,
        int $fileId,
        string $sessionId,
    ): array {
        return DB::transaction(function () use (
            $user, $device, $accessId, $contentType, $contentId, $fileId, $sessionId
        ): array {
            $access = PurchaseAccess::query()
                ->withoutGlobalScope(ActivePurchaseAccessScope::class)
                ->whereKey($accessId)
                ->where('user_id', $user->getKey())
                ->whereHas(
                    'productSaleItem',
                    fn($query) => $query->where('launcher_enabled', true)
                )
                ->lockForUpdate()
                ->firstOrFail();

            if (!$access->isUsable()) {
                throw new PurchaseAccessIsNotUsableException();
            }

            [$content, $file] = $this->resolve(
                $access->product_sale_item_id,
                $contentType,
                $contentId,
                $fileId,
            );

            $download = LauncherDownloadSession::query()
                ->where('purchase_access_id', $access->getKey())
                ->where('launcher_device_id', $device->getKey())
                ->where('content_type', $contentType)
                ->where('file_id', $fileId)
                ->where('download_session_id', $sessionId)
                ->first();

            $reused = $download !== null;

            if ($download && $download->session_expires_at->isPast()) {
                throw new PurchaseAccessDownloadSessionExpiredException();
            }

            if (!$download) {
                $download = LauncherDownloadSession::query()->create([
                    'purchase_access_id' => $access->getKey(),
                    'launcher_device_id' => $device->getKey(),
                    'content_type' => $contentType,
                    'content_id' => $contentId,
                    'file_id' => $fileId,
                    'download_session_id' => $sessionId,
                    'session_expires_at' => now()->addMinutes(
                        max(1, (int)config('launcher.download_session_ttl_minutes', 1440))
                    ),
                    'last_prepared_at' => now(),
                    'prepare_count' => 1,
                ]);
            } else {
                $download->forceFill([
                    'last_prepared_at' => now(),
                    'prepare_count' => $download->prepare_count + 1,
                ])->save();
            }

            $this->validateSourceUrl($file->url);

            $url = URL::temporarySignedRoute(
                'downloads.launcher-content.file',
                now()->addMinutes(max(1, (int)config('launcher.download_url_ttl_minutes', 10))),
                [
                    'purchaseAccess' => $access->getKey(),
                    'contentType' => $contentType,
                    'content' => $content->getKey(),
                    'file' => $file->getKey(),
                ],
            );

            return [
                'download_id' => $download->getKey(),
                'download_session_id' => $download->download_session_id,
                'session_expires_at' => $download->session_expires_at->toISOString(),
                'reused_session' => $reused,
                'prepare_count' => $download->prepare_count,
                'url' => $url,
                'file_name' => $file->file_name,
                'title' => $file->title,
                'size_bytes' => $file->size_bytes,
                'sha256' => strtolower($file->sha256),
            ];
        });
    }

    private function resolve(
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

    private function validateSourceUrl(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false
            || strtolower((string)parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            throw new PurchaseAccessFileUnavailableException();
        }

        $allowed = config('launcher.allowed_download_hosts', []);
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));

        if ($allowed !== [] && !in_array($host, array_map('strtolower', $allowed), true)) {
            throw new PurchaseAccessFileUnavailableException();
        }
    }
}
