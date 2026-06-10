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
                userId: (int)$request->user()->id,
            );

            $downloadUrl = $this->resolveDownloadUrl($file);

            if (!$downloadUrl) {
                abort(404, 'File URL not found.');
            }

            /**
             * از این لحظه دانلود را موفق حساب می‌کنیم؛
             * چون access معتبر است و کاربر به لینک دانلود خارجی هدایت می‌شود.
             */
            $recordPurchaseAccessDownloadAction->execute(
                purchaseAccess: $purchaseAccess,
                file: $file,
                request: $request,
                metadata: [
                    'source' => 'external_redirect_download',
                    'signed_url_expires' => $request->query('expires'),
                ],
            );

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
}
