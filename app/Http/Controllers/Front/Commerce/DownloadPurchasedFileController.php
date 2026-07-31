<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Application\Commerce\Actions\Access\EnsurePurchaseAccessCanDownloadFileAction;
use App\Application\Commerce\Actions\Access\RecordPurchaseAccessDownloadAction;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class DownloadPurchasedFileController extends Controller
{
    public function __invoke(
        PurchaseAccess                            $purchaseAccess,
        ProductSaleItemFile                       $file,
        Request                                   $request,
        EnsurePurchaseAccessCanDownloadFileAction $ensurePurchaseAccessCanDownloadFileAction,
        RecordPurchaseAccessDownloadAction        $recordPurchaseAccessDownloadAction,
    ): RedirectResponse
    {
        try {
            $ensurePurchaseAccessCanDownloadFileAction->execute(
                purchaseAccess: $purchaseAccess,
                file: $file,
                userId: (int)$purchaseAccess->user_id,
            );

            $downloadUrl = $this->resolveDownloadUrl($file);

            if (!$downloadUrl) {
                abort(404, 'File URL not found.');
            }

            $this->ensureSafeDownloadUrl($downloadUrl);

            /*
             * The launcher prepare endpoint has already created the
             * idempotent download session and consumed quota. Browser
             * downloads are recorded here because they bypass prepare.
             */
            if (!$request->boolean('prepared')) {
                $recordPurchaseAccessDownloadAction->execute(
                    purchaseAccess: $purchaseAccess,
                    file: $file,
                    request: $request,
                    metadata: [
                        'source' => 'external_redirect_download',
                        'signed_url_expires' => $request->query('expires'),
                    ],
                );
            }

            return redirect()->away($downloadUrl);
        } catch (DomainException $exception) {
            abort(403, 'You are not allowed to download this file.');
        } catch (Throwable $exception) {
            report($exception);

            abort(500, 'Download failed.');
        }
    }

    private function resolveDownloadUrl(ProductSaleItemFile $file): ?string
    {
        return $file->url ?: null;
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
            throw new DomainException('Unsafe download URL.');
        }
    }
}
