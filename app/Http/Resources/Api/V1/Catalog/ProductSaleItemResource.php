<?php

namespace App\Http\Resources\Api\V1\Catalog;

use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = app(ResolvePurchasablePriceAction::class)
            ->execute($this->resource);

        return [
            'id' => $this->id,
            'sku' => $this->sku,

            'status' => $this->status->value,
            'is_available' => $this->isPurchasable(),

            'description' => $this->description,

            'download_limit' => $this->download_limit,
            'download_expires_in_days' => $this->download_expires_in_days,
            'max_quantity_per_order' => $this->max_quantity_per_order,

            'pricing' => [
                ...$price->toSnapshot(),
                'currency' => 'IRT',
            ],

            'user_access' => [
                'authenticated' => (bool)$this->getAttribute('viewer_authenticated'),
                'is_purchased' => $this->getAttribute('is_purchased'),
                'has_usable_access' => $this->getAttribute('has_usable_access'),
                'purchase_access_id' => $this->getAttribute('purchase_access_id'),
            ],

            'attributes' => $this->when(
                $this->relationLoaded('attributeValues'),
                function () {
                    return $this->attributeValues
                        ->filter(
                            static fn($attributeValue): bool => $attributeValue->isGlobal()
                                && $attributeValue->globalValue !== null
                        )
                        ->map(
                            static function ($attributeValue): array {
                                return [
                                    'attribute_id' => $attributeValue->globalValue->product_attribute_id,
                                    'attribute_title' => $attributeValue->globalValue->attribute?->title,
                                    'value_id' => $attributeValue->globalValue->id,
                                    'value_title' => $attributeValue->globalValue->title,
                                ];
                            }
                        )
                        ->values();
                }
            ),

            'sort_order' => $this->sort_order,
        ];
    }
}
