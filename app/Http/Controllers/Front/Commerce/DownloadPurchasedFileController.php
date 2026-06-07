<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Application\Commerce\Actions\Access\EnsurePurchaseAccessCanDownloadFileAction;
use App\Application\Commerce\Actions\Access\RecordPurchaseAccessDownloadAction;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DownloadPurchasedFileController extends Controller
{
    public function __invoke(
        PurchaseAccess                            $purchaseAccess,
        ProductSaleItemFile                       $file,
        Request                                   $request,
        EnsurePurchaseAccessCanDownloadFileAction $ensurePurchaseAccessCanDownloadFileAction,
        RecordPurchaseAccessDownloadAction        $recordPurchaseAccessDownloadAction,
    ): StreamedResponse
    {
        try {
            $ensurePurchaseAccessCanDownloadFileAction->execute(
                purchaseAccess: $purchaseAccess,
                file: $file,
                userId: (int)$request->user()->id,
            );

            $disk = $this->resolveDisk($file);
            $path = $this->resolvePath($file);

            if (!$path || !Storage::disk($disk)->exists($path)) {
                abort(404, 'File not found.');
            }

            /**
             * از این لحظه دانلود را موفق حساب می‌کنیم؛
             * چون access معتبر است، فایل وجود دارد و response دانلود قرار است شروع شود.
             */
            $recordPurchaseAccessDownloadAction->execute(
                purchaseAccess: $purchaseAccess,
                file: $file,
                request: $request,
                metadata: [
                    'source' => 'secure_download_route',
                    'signed_url_expires' => $request->query('expires'),
                ],
            );

            return Storage::disk($disk)->download(
                path: $path,
                name: $this->resolveDownloadName($file),
            );
        } catch (DomainException $exception) {
            abort(403, 'You are not allowed to download this file.');
        } catch (Throwable $exception) {
            report($exception);

            abort(500, 'Download failed.');
        }
    }

    private function resolveDisk(ProductSaleItemFile $file): string
    {
        return $file->disk
            ?? $file->file_disk
            ?? config('filesystems.default');
    }

    private function resolvePath(ProductSaleItemFile $file): ?string
    {
        return $file->path
            ?? $file->file_path
            ?? null;
    }

    private function resolveDownloadName(ProductSaleItemFile $file): string
    {
        return $file->file_name
            ?? $file->name
            ?? $file->title
            ?? basename((string)$this->resolvePath($file))
            ?? 'download-file';
    }
}
