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
        $isFree = $price->finalPrice === 0;

        $saleItemAccesses = $this->purchaseAccesses
            ->filter(
                fn(PurchaseAccess $access): bool =>
                    (int)$access->product_sale_item_id === (int)$this->id
            )
            ->values();

        /** @var PurchaseAccess|null $usableAccess */
        $usableAccess = $saleItemAccesses
            ->first(fn(PurchaseAccess $access): bool => $access->isUsable());

        // A cancelled/refunded/revoked historical order must not unlock the
        // launcher.  Ownership is deliberately based only on a usable access.
        $hasAccess = $usableAccess instanceof PurchaseAccess;

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
                'can_buy' => !$isFree && !$hasAccess,
                'can_claim' => $isFree && !$hasAccess,
                'can_install' => $hasAccess,
            ],
            'web_url' => $this->product?->url,
            'capabilities' => $this->when(
                $this->relationLoaded('manifest') && $this->manifest !== null,
                fn() => $this->manifest->resolvedCapabilities(),
            ),
            'support' => [
                'enabled' => (bool)$this->launcher_support_enabled,
                'url' => $this->launcher_support_enabled ? $this->launcher_support_url : null,
            ],
        ];
    }
}
