<?php

namespace App\Http\Resources\Api\V1\Launcher;

use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use App\Domain\Commerce\Models\PurchaseAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LauncherCatalogItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = app(ResolvePurchasablePriceAction::class)->execute($this->resource);
        $isFree = (bool) $this->launcher_free_claim_enabled
            && $price->finalPrice === 0;

        $saleItemAccesses = $this->purchaseAccesses
            ->filter(
                fn(PurchaseAccess $access): bool =>
                    (int)$access->product_sale_item_id === (int)$this->id
            )
            ->values();

        /** @var PurchaseAccess|null $usableAccess */
        $usableAccess = $saleItemAccesses
            ->first(fn(PurchaseAccess $access): bool => $access->isUsableForLauncher());

        // A cancelled/refunded/revoked historical order must not unlock the
        // launcher.  Ownership is deliberately based only on a usable access.
        $hasAccess = $usableAccess instanceof PurchaseAccess;
        $isPurchasable = $this->resource->isPurchasable();

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'title' => $this->launcher_title ?: ($this->title ?: $this->product?->title),
            'excerpt' => $this->launcher_excerpt ?: $this->description,
            'version' => $this->version,
            'edition_key' => $this->edition_key,
            'hero_url' => $this->launcherHero?->url,
            'card_image_url' => $this->product?->picture?->url,
            'is_free' => $isFree,
            'ownership' => [
                'owned' => $hasAccess,
                'has_access' => $hasAccess,
                'access_id' => $usableAccess?->id,
            ],
            'actions' => [
                'can_buy' => $isPurchasable && !$isFree && !$hasAccess,
                'can_claim' => $isPurchasable && $isFree && !$hasAccess,
                'can_install' => $hasAccess,
            ],
            'web_url' => $this->product?->url,
            'capabilities' => $this->when(
                $hasAccess && $this->relationLoaded('manifest') && $this->manifest !== null,
                fn() => $this->manifest->resolvedCapabilities(),
            ),
            // Installation metadata is intentionally exposed only to owners.
            // The launcher uses these values for preflight disk checks and
            // must never reveal archive/install requirements for locked items.
            'installation' => $this->when(
                $hasAccess && $this->relationLoaded('manifest') && $this->manifest !== null,
                fn() => [
                    'options' => $this->manifest->resolvedInstallOptions(),
                    'required_install_space_bytes' => data_get(
                        $this->manifest->resolvedInstallOptions(),
                        'required_install_space_bytes'
                    ),
                    'estimated_installed_size_bytes' => data_get(
                        $this->manifest->resolvedInstallOptions(),
                        'estimated_installed_size_bytes'
                    ),
                ],
            ),
            'support' => [
                'enabled' => $hasAccess && (bool)$this->launcher_support_enabled,
                'url' => $hasAccess && $this->launcher_support_enabled
                    ? $this->launcher_support_url
                    : null,
            ],
            'usage_guide' => [
                'enabled' => $hasAccess && (bool)$this->launcher_usage_guide_enabled,
                'content' => $hasAccess && $this->launcher_usage_guide_enabled
                    ? $this->launcher_usage_guide_content
                    : null,
            ],
        ];
    }
}
