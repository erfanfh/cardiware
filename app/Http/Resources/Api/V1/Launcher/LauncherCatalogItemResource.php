<?php

namespace App\Http\Resources\Api\V1\Launcher;

use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LauncherCatalogItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = app(ResolvePurchasablePriceAction::class)->execute($this->resource);
        $access = $this->purchaseAccesses
            ->first(fn($item): bool => $item->isUsable());
        $isFree = $price->finalPrice === 0;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'title' => $this->launcher_title ?: ($this->title ?: $this->product?->title),
            'excerpt' => $this->launcher_excerpt ?: $this->description,
            'version' => $this->version,
            'edition_key' => $this->edition_key,
            'hero_url' => $this->product?->picture?->url,
            'is_free' => $isFree,
            'ownership' => [
                'owned' => $access !== null,
                'access_id' => $access?->id,
            ],
            'actions' => [
                'can_buy' => !$isFree && $access === null,
                'can_claim' => $isFree && $access === null,
                'can_install' => $access !== null,
            ],
            'web_url' => $this->product?->url,
            'capabilities' => $this->when(
                $this->relationLoaded('manifest') && $this->manifest !== null,
                fn() => $this->manifest->resolvedCapabilities(),
            ),
            'support' => [
                'enabled' => false,
                'url' => null,
            ],
        ];
    }
}
