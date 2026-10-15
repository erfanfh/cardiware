<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\GetLauncherModsAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Catalog\Models\ProductSaleItem;
use Illuminate\Support\Facades\Cache;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LauncherContentController extends Controller
{
    public function updates(Request $request, int $purchaseAccess): JsonResponse
    {
        $access = $this->access($request, $purchaseAccess);

        // Cache separately for testers and ordinary users. Short
        // TTL because published() is a "published_at <= now()" window: a scheduled
        // publish must appear within minutes even without an edit.
        $saleItemId = (int) $access->product_sale_item_id;
        $saleItem = $access->productSaleItem()->firstOrFail();
        $revision = (int) $saleItem->manifest_revision;
        $userId = (int) $request->user()->getKey();
        $isTester = $saleItem->isLauncherStagingTester($userId);
        $updates = Cache::tags([
            CacheTag::LAUNCHER,
            CacheTag::LAUNCHER_CONTENT,
            CacheTag::launcherSaleItem($saleItemId),
        ])->remember(
            CacheKey::launcherUpdates($saleItemId, $revision, $isTester),
            now()->addMinutes(5),
            fn(): array => $this->buildUpdates($saleItem, $userId),
        );

        return response()->json([
            'data' => $updates,
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildUpdates(ProductSaleItem $saleItem, int $userId): array
    {
        $updates = LauncherUpdate::query()
            ->where('product_sale_item_id', $saleItem->getKey())
            ->visibleToLauncherUser($userId, $saleItem->staging_user_ids ?? [])
            ->published()
            ->with('files')
            ->get()
            ->filter(fn(LauncherUpdate $update): bool => $update->hasDownloadablePackage())
            ->sort(function (LauncherUpdate $left, LauncherUpdate $right): int {
                $publishedAtComparison = ($left->published_at?->getTimestamp() ?? 0)
                    <=> ($right->published_at?->getTimestamp() ?? 0);

                if ($publishedAtComparison !== 0) {
                    return $publishedAtComparison;
                }

                $versionComparison = version_compare($left->version, $right->version);

                return $versionComparison !== 0
                    ? $versionComparison
                    : $left->getKey() <=> $right->getKey();
            })
            ->values()
            ->map(function (LauncherUpdate $update): array {
                $files = $update->files->values();

                return [
                    'id' => $update->id,
                    'version' => $update->version,
                    'title' => $update->title,
                    'description' => $update->description,
                    'changelog' => $update->changelog,
                    'is_mandatory' => $update->is_mandatory,
                    'has_option_file' => $update->has_option_file,
                    'option_file' => $update->resolvedOptionFile(),
                    'published_at' => $update->published_at?->toISOString(),
                    'package' => [
                        'archive_format' => $update->archive_format,
                        'archive_password' => $update->archive_password,
                        'merge_mode' => 'merge_to_product_root',
                        'total_size_bytes' => (int)$files->sum(
                            static fn($file): int => (int)$file->size_bytes
                        ),
                        'files' => $files
                            ->map(fn($file): array => $this->file($file))
                            ->values(),
                    ],
                ];
            });

        return $updates->all();
    }

    public function mods(
        Request $request,
        int $purchaseAccess,
        GetLauncherModsAction $action,
    ): JsonResponse {
        $access = $this->access($request, $purchaseAccess);

        $saleItemId = (int) $access->product_sale_item_id;
        $saleItem = $access->productSaleItem()->firstOrFail();
        $revision = (int) $saleItem->manifest_revision;
        $isTester = $saleItem->isLauncherStagingTester((int) $request->user()->getKey());
        // The action must use the same tester membership snapshot as this cache key.
        $access->setRelation('productSaleItem', $saleItem);
        $mods = Cache::tags([
            CacheTag::LAUNCHER,
            CacheTag::LAUNCHER_CONTENT,
            CacheTag::launcherSaleItem($saleItemId),
        ])->remember(
            CacheKey::launcherMods($saleItemId, $revision, $isTester),
            now()->addMinutes(5),
            fn(): array => $action->execute($access),
        );

        return response()->json([
            'data' => $mods,
        ])->header('Cache-Control', 'private, no-store, max-age=0');
    }

    private function access(Request $request, int $id): PurchaseAccess
    {
        $access = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($id)
            ->where('user_id', $request->user()->getKey())
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->firstOrFail();

        abort_unless($access->isUsableForLauncher(), 403);

        return $access;
    }

    private function file(mixed $file): array
    {
        return [
            'file_id' => $file->id,
            'file_name' => $file->file_name,
            'title' => $file->title,
            'size_bytes' => (int)$file->size_bytes,
            'sha256' => strtolower((string)$file->sha256),
            'mime_type' => $file->mime_type,
            'part_number' => $file->part_number,
            'is_required' => $file->is_required,
            'order' => $file->sort_order,
        ];
    }
}
