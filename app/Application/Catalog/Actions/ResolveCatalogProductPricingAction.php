<?php

namespace App\Application\Catalog\Actions;

use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use App\Domain\Catalog\Models\ProductSaleItem;
use Illuminate\Support\Collection;

class ResolveCatalogProductPricingAction
{
    public function __construct(
        private readonly ResolvePurchasablePriceAction $resolvePriceAction,
    )
    {
    }

    /**
     * @param Collection<int, ProductSaleItem> $saleItems
     *
     * @return array<string, mixed>
     */
    public function execute(Collection $saleItems): array
    {
        $availableSaleItems = $saleItems
            ->filter(
                static fn(ProductSaleItem $saleItem): bool => $saleItem->isPurchasable()
            );

        if ($availableSaleItems->isEmpty()) {
            return [
                'minimum_price' => null,
                'maximum_price' => null,
                'has_free_option' => false,
                'currency' => 'IRT',
            ];
        }

        $finalPrices = $availableSaleItems
            ->map(
                fn(ProductSaleItem $saleItem): int => $this->resolvePriceAction
                    ->execute($saleItem)
                    ->finalPrice
            );

        return [
            'minimum_price' => $finalPrices->min(),
            'maximum_price' => $finalPrices->max(),
            'has_free_option' => $finalPrices->contains(0),
            'currency' => 'IRT',
        ];
    }
}
