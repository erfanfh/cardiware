<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadLauncherContentFileController extends Controller
{
    public function __invoke(
        PurchaseAccess $purchaseAccess,
        string $contentType,
        int $content,
        int $file,
    ): RedirectResponse {
        abort_unless($purchaseAccess->isUsableForLauncher(), 403);

        if ($contentType === 'update') {
            $saleItem = $purchaseAccess->productSaleItem()->firstOrFail();
            $owner = LauncherUpdate::query()
                ->whereKey($content)
                ->where('product_sale_item_id', $purchaseAccess->product_sale_item_id)
                ->visibleToLauncherUser((int) $purchaseAccess->user_id, $saleItem->staging_user_ids ?? [])
                ->published()
                ->firstOrFail();
            $download = LauncherUpdateFile::query()
                ->whereKey($file)
                ->where('launcher_update_id', $owner->getKey())
                ->firstOrFail();
        } elseif ($contentType === 'mod') {
            $owner = LauncherMod::query()
                ->whereKey($content)
                ->where('product_sale_item_id', $purchaseAccess->product_sale_item_id)
                ->published()
                ->firstOrFail();
            $download = LauncherModFile::query()
                ->whereKey($file)
                ->where('launcher_mod_id', $owner->getKey())
                ->firstOrFail();
        } else {
            abort(404);
        }

        $this->ensureSafeDownloadUrl($download->url);

        return redirect()->away($download->url);
    }

    private function ensureSafeDownloadUrl(string $url): void
    {
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $allowed = array_map(
            static fn(string $value): string => strtolower(trim($value)),
            config('launcher.allowed_download_hosts', [])
        );

        if (filter_var($url, FILTER_VALIDATE_URL) === false
            || $scheme !== 'https'
            || ($allowed !== [] && !in_array($host, $allowed, true))) {
            throw new HttpException(503, 'Download file is unavailable.');
        }
    }
}
