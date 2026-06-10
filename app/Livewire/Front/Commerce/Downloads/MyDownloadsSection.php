<?php

namespace App\Livewire\Front\Commerce\Downloads;

use App\Application\Commerce\Actions\Access\GeneratePurchaseAccessDownloadUrlAction;
use App\Application\Commerce\Actions\Access\GetUserPurchaseAccessesAction;
use App\Application\Commerce\Actions\Access\HasPurchaseAccessFileReachedDownloadLimitAction;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MyDownloadsSection extends Component
{
    /**
     * Get current user's purchase accesses.
     *
     * @return Collection<int, PurchaseAccess>
     */
    #[Computed]
    public function purchaseAccesses(): Collection
    {
        if (!auth()->check()) {
            return collect();
        }

        return app(GetUserPurchaseAccessesAction::class)->execute(
            userId: (int)auth()->id(),
        );
    }

    /**
     * Generate signed download URL for a file.
     */
    public function downloadUrl(int $purchaseAccessId, int $fileId): string
    {
        $purchaseAccess = PurchaseAccess::query()
            ->where('user_id', auth()->id())
            ->findOrFail($purchaseAccessId);

        $file = ProductSaleItemFile::query()
            ->findOrFail($fileId);

        return app(GeneratePurchaseAccessDownloadUrlAction::class)->execute(
            purchaseAccess: $purchaseAccess,
            file: $file,
            expiresInMinutes: 10,
        );
    }

    /**
     * Resolve product page URL.
     */
    public function productUrl(PurchaseAccess $access): string
    {
        $product = $access->product;

        if (!$product) {
            return '#';
        }

        return route('products.detail', $product);
    }

    /**
     * Resolve purchase access title.
     */
    public function accessTitle(PurchaseAccess $access): string
    {
        return $access->product?->title
            ?? data_get($access->metadata, 'order_item_snapshot.title')
            ?? 'محصول خریداری‌شده';
    }

    /**
     * Resolve purchase access image.
     */
    public function accessImage(PurchaseAccess $access): string
    {
        return $access->product?->picture?->url
            ?? $access->product?->picture?->primaryVariant?->url
            ?? data_get($access->metadata, 'order_item_snapshot.image_url')
            ?? asset('front/assets/images/placeholder.webp');
    }

    /**
     * Resolve access status label.
     */
    public function accessStatusLabel(PurchaseAccess $access): string
    {
        if ($access->isExpiredByDate()) {
            return 'منقضی شده';
        }

        if (!$access->status->isUsable()) {
            return $access->status->label();
        }

        return 'فعال';
    }

    /**
     * Resolve access status badge class.
     */
    public function accessStatusClass(PurchaseAccess $access): string
    {
        if ($access->isExpiredByDate()) {
            return 'text-red-700 bg-red-50 border-red-200';
        }

        if (!$access->status->isUsable()) {
            return 'text-amber-700 bg-amber-50 border-amber-200';
        }

        return 'text-emerald-700 bg-emerald-50 border-emerald-200';
    }

    /**
     * Resolve expiration label.
     */
    public function expiresLabel(PurchaseAccess $access): string
    {
        if ($access->expires_at === null) {
            return 'بدون تاریخ انقضا';
        }

        if ($access->expires_at->isPast()) {
            return 'منقضی شده در ' . $access->expires_at->format('Y/m/d');
        }

        return 'اعتبار تا ' . $access->expires_at->format('Y/m/d');
    }

    /**
     * Resolve last downloaded label.
     */
    public function lastDownloadedLabel(PurchaseAccess $access): string
    {
        if ($access->last_downloaded_at === null) {
            return 'هنوز دانلود نشده';
        }

        return 'آخرین دانلود: ' . $access->last_downloaded_at->format('Y/m/d H:i');
    }

    /**
     * Resolve file title.
     */
    public function fileTitle(ProductSaleItemFile $file): string
    {
        return $file->title
            ?? $file->name
            ?? $file->file_name
            ?? basename((string)($file->path ?? $file->file_path ?? $file->url ?? ''))
            ?: 'فایل دانلود';
    }

    /**
     * Resolve file size label.
     */
    public function fileSizeLabel(ProductSaleItemFile $file): ?string
    {
        $size = $file->size
            ?? $file->file_size
            ?? $file->size_bytes
            ?? null;

        if ($size === null) {
            return null;
        }

        $size = (int)$size;

        if ($size >= 1024 * 1024 * 1024) {
            return round($size / 1024 / 1024 / 1024, 2) . 'GB';
        }

        if ($size >= 1024 * 1024) {
            return round($size / 1024 / 1024, 1) . 'MB';
        }

        if ($size >= 1024) {
            return round($size / 1024, 1) . 'KB';
        }

        return $size . 'B';
    }

    /**
     * Resolve remaining downloads label for a specific file.
     */
    public function fileRemainingDownloadsLabel(PurchaseAccess $access, ProductSaleItemFile $file): string
    {
        $remaining = app(HasPurchaseAccessFileReachedDownloadLimitAction::class)->remaining(
            purchaseAccess: $access,
            file: $file,
        );

        if ($remaining === null) {
            return 'دانلود نامحدود';
        }

        return $remaining . ' دانلود باقی‌مانده';
    }

    /**
     * Determine whether a specific file is downloadable.
     */
    public function fileIsUsable(PurchaseAccess $access, ProductSaleItemFile $file): bool
    {
        if (!$access->status->isUsable()) {
            return false;
        }

        if ($access->isExpiredByDate()) {
            return false;
        }

        return !app(HasPurchaseAccessFileReachedDownloadLimitAction::class)->execute(
            purchaseAccess: $access,
            file: $file,
        );
    }

    /**
     * Determine whether access has installation steps.
     */
    public function hasInstallationSteps(PurchaseAccess $access): bool
    {
        return $access->product?->installationSteps?->isNotEmpty() ?? false;
    }

    public function render(): View
    {
        return view('livewire.front.commerce.downloads.my-downloads-section');
    }
}
