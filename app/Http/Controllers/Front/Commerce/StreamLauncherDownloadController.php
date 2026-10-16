<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Application\Launcher\Actions\SignDirectLauncherDownloadUrlAction;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StreamLauncherDownloadController extends Controller
{
    public function __invoke(
        Request $request,
        int $purchaseAccess,
        string $contentType,
        int $content,
        int $file,
        string $downloadSessionId,
        RemoteDownloadFileMetadataResolver $metadataResolver,
        SignDirectLauncherDownloadUrlAction $signDirectDownloadUrlAction,
    ): Response {
        $access = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($purchaseAccess)
            ->firstOrFail();

        abort_unless($access->isUsableForLauncher(), 403);
        $access->loadMissing('user');
        abort_if(
            $access->user === null || $access->user->isLauncherSuspended(),
            403,
            'Launcher account is suspended.',
        );

        $signedManifestRevision = filter_var(
            $request->query('manifestRevision'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]],
        );
        abort_if($signedManifestRevision === false, 410, 'Download manifest is no longer valid.');

        $currentManifestRevision = (int) ProductSaleItem::query()
            ->withTrashed()
            ->whereKey($access->product_sale_item_id)
            ->value('manifest_revision');
        abort_if(
            $signedManifestRevision !== $currentManifestRevision,
            410,
            'Download manifest is no longer valid.',
        );

        [$sourceType, $downloadFile, $sourceUrl, $downloadSession] = $this->resolveDownload(
            access: $access,
            contentType: $contentType,
            contentId: $content,
            fileId: $file,
            sessionId: strtolower($downloadSessionId),
        );
        $this->ensureSessionDeviceIsActive($downloadSession, $access);

        $metadata = $metadataResolver->resolve(
            sourceType: $sourceType,
            file: $downloadFile,
            url: $sourceUrl,
        );
        $this->ensureSessionMatchesCurrentFile(
            session: $downloadSession,
            file: $downloadFile,
            sourceUrl: $sourceUrl,
            metadata: $metadata,
            manifestRevision: $currentManifestRevision,
        );

        // Keep the signed endpoint (including already-issued URLs) as the access
        // gate, but let the download host serve GET/HEAD and byte ranges itself.
        return redirect()->away(
            $signDirectDownloadUrlAction->execute($sourceUrl, $access->user),
            307,
            ['Cache-Control' => 'private, no-store, max-age=0'],
        );
    }

    /**
     * @return array{0:string,1:Model,2:string,3:Model}
     */
    private function resolveDownload(
        PurchaseAccess $access,
        string $contentType,
        int $contentId,
        int $fileId,
        string $sessionId,
    ): array {
        if ($contentType === 'product') {
            abort_unless($contentId === (int)$access->product_sale_item_id, 404);

            $downloadSession = PurchaseAccessDownload::query()
                ->where('purchase_access_id', $access->getKey())
                ->where('product_sale_item_file_id', $fileId)
                ->where('download_session_id', $sessionId)
                ->firstOrFail();

            $this->ensureSessionIsAlive($downloadSession->session_expires_at);

            $file = ProductSaleItemFile::query()
                ->whereKey($fileId)
                ->where('product_sale_item_id', $access->product_sale_item_id)
                ->where('is_active', true)
                ->firstOrFail();

            return ['product', $file, (string)$file->url, $downloadSession];
        }

        $downloadSession = LauncherDownloadSession::query()
            ->where('purchase_access_id', $access->getKey())
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('file_id', $fileId)
            ->where('download_session_id', $sessionId)
            ->firstOrFail();

        $this->ensureSessionIsAlive($downloadSession->session_expires_at);

        if ($contentType === 'update') {
            $saleItem = $access->productSaleItem()->firstOrFail();
            $owner = LauncherUpdate::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $access->product_sale_item_id)
                ->visibleToLauncherUser((int) $access->user_id, $saleItem->staging_user_ids ?? [])
                ->published()
                ->firstOrFail();

            $file = LauncherUpdateFile::query()
                ->whereKey($fileId)
                ->where('launcher_update_id', $owner->getKey())
                ->firstOrFail();

            return ['update', $file, (string)$file->url, $downloadSession];
        }

        if ($contentType === 'mod') {
            $owner = LauncherMod::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $access->product_sale_item_id)
                ->published()
                ->firstOrFail();

            $file = LauncherModFile::query()
                ->whereKey($fileId)
                ->where('launcher_mod_id', $owner->getKey())
                ->firstOrFail();

            return ['mod', $file, (string)$file->url, $downloadSession];
        }

        abort(404);
    }

    private function ensureSessionIsAlive(mixed $expiresAt): void
    {
        abort_if($expiresAt === null || $expiresAt->isPast(), 410, 'Download session has expired.');
    }

    private function ensureSessionDeviceIsActive(Model $session, PurchaseAccess $access): void
    {
        $deviceId = (int) $session->getAttribute('launcher_device_id');
        abort_if($deviceId <= 0, 410, 'Download session is not bound to an active device.');

        $device = LauncherDevice::query()
            ->whereKey($deviceId)
            ->where('user_id', $access->user_id)
            ->first();
        $user = $access->user;
        $activeSlot = (int) ($user?->active_device_slot ?: 0);

        abort_if(
            !$device instanceof LauncherDevice
            || !$device->isActive()
            || $activeSlot === 0
            || (int) $device->device_slot !== $activeSlot,
            410,
            'Download session device is no longer active.',
        );

        $activePublicKey = $activeSlot === 2
            ? $user->second_device_public_key
            : $user->first_device_public_key;
        $decodedKey = base64_decode((string) $activePublicKey, true);

        abort_if(
            $decodedKey === false
            || !hash_equals(
                hash('sha256', $decodedKey),
                (string) $device->public_key_fingerprint,
            ),
            410,
            'Download session device identity is no longer valid.',
        );
    }

    /**
     * @param array{size_bytes:int,sha256:string,mime_type:?string} $metadata
     */
    private function ensureSessionMatchesCurrentFile(
        Model $session,
        Model $file,
        string $sourceUrl,
        array $metadata,
        int $manifestRevision,
    ): void {
        abort_if(
            $session->getAttribute('invalidated_at') !== null
            || (int) $session->getAttribute('manifest_revision') !== $manifestRevision
            || (int) $session->getAttribute('file_size_bytes') !== $metadata['size_bytes']
            || !hash_equals(
                strtolower((string) $session->getAttribute('file_sha256')),
                strtolower($metadata['sha256']),
            )
            || !hash_equals(
                (string) $session->getAttribute('file_name'),
                $this->resolveFileName($file, $sourceUrl),
            ),
            410,
            'Download manifest is no longer valid.',
        );
    }

    private function resolveFileName(Model $file, string $url): string
    {
        $explicit = trim((string)($file->getAttribute('file_name') ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        $path = (string)parse_url($url, PHP_URL_PATH);
        $fromUrl = basename(rawurldecode($path));

        if ($fromUrl !== '' && $fromUrl !== '/' && $fromUrl !== '.') {
            return $fromUrl;
        }

        $title = trim((string)($file->getAttribute('title') ?? ''));

        return $title !== '' ? $title : 'download.bin';
    }
}
