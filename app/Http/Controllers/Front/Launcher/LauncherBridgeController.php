<?php

namespace App\Http\Controllers\Front\Launcher;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LauncherBridgeController extends Controller
{
    public function product(int $saleItem): View
    {
        $item = ProductSaleItem::query()
            ->whereKey($saleItem)
            ->where('launcher_enabled', true)
            ->with('product')
            ->firstOrFail();

        return $this->render(
            title: $item->launcher_title ?: ($item->title ?: ($item->product?->title ?: 'محصول Cardiware')),
            description: 'برای ادامه دانلود و نصب، این محصول را در MW Launcher باز کنید.',
            protocolUrl: 'mwlauncher://product/' . $item->getKey() . '?refresh=1',
        );
    }

    public function purchaseComplete(Request $request): View
    {
        $state = (string)$request->query('state');
        abort_if(strlen($state) < 32, 404);

        return $this->render(
            title: 'خرید با موفقیت انجام شد',
            description: 'لانچر را باز کنید تا دسترسی محصول بررسی و مراحل دانلود فعال شود.',
            protocolUrl: 'mwlauncher://purchase-complete?state=' . rawurlencode($state),
            autoOpen: true,
        );
    }

    private function render(
        string $title,
        string $description,
        string $protocolUrl,
        bool $autoOpen = false,
    ): View {
        return view('front.launcher.bridge', [
            'title' => $title,
            'description' => $description,
            'protocolUrl' => $protocolUrl,
            'autoOpen' => $autoOpen,
            'downloadUrl' => $this->publicDownloadUrl(),
        ]);
    }

    private function publicDownloadUrl(): ?string
    {
        $stored = trim((string)get_setting('general', 'launcher_public_download_url', ''));
        $fallback = trim((string)config('launcher.public_download_url', ''));

        return $stored !== '' ? $stored : ($fallback !== '' ? $fallback : null);
    }
}
