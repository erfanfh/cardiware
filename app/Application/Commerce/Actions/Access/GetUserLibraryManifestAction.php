<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Catalog\Actions\BuildPurchaseAccessManifestAction;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Cache;

class GetUserLibraryManifestAction
{
    public function __construct(
        private readonly BuildPurchaseAccessManifestAction $buildManifestAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, int $purchaseAccessId): array
    {
        $purchaseAccess = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($purchaseAccessId)
            ->where('user_id', $user->getKey())
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->firstOrFail();

        if (!$purchaseAccess->isUsableForLauncher()) {
            throw new PurchaseAccessIsNotUsableException();
        }

        // Everything in the manifest except the 'access' block is a function of the sale
        // item at one manifest_revision (files, install/launch options, DRM policy), and
        // building it walks every file row. Cache it keyed by (sale item, revision) -- a
        // bump changes the key, and BumpLauncherManifestRevisionAction flushes the tag --
        // then overwrite the per-access block from the row just loaded.
        $saleItemId = (int) $purchaseAccess->product_sale_item_id;
        $revision = (int) $purchaseAccess->productSaleItem()->value('manifest_revision');

        $manifest = Cache::tags([
            CacheTag::LAUNCHER,
            CacheTag::LAUNCHER_MANIFEST,
            CacheTag::launcherSaleItem($saleItemId),
        ])->remember(
            CacheKey::launcherManifest($saleItemId, $revision),
            CacheTtl::medium(),
            fn(): array => $this->buildManifestAction->execute($purchaseAccess),
        );

        $manifest['access'] = [
            'id' => $purchaseAccess->id,
            'is_usable' => true,
            'starts_at' => $purchaseAccess->starts_at?->toISOString(),
            'expires_at' => $purchaseAccess->expires_at?->toISOString(),
        ];

        return $manifest;
    }
}
