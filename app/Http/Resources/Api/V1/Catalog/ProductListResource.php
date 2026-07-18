<?php

namespace App\Http\Resources\Api\V1\Catalog;

use App\Application\Catalog\Actions\ResolveCatalogProductPricingAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pricing = app(ResolveCatalogProductPricingAction::class)->execute($this->saleItems);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'excerpt' => $this->excerpt,

            'type' => $this->type->value,
            'content_type' => $this->content_type->value,
            'structure' => $this->structure->value,

            'cover' => $this->picture
                ? [
                    'id' => $this->picture->id,
                    'url' => $this->picture->url,
                ]
                : null,

            'store' => $this->store
                ? [
                    'id' => $this->store->id,
                    'title' => $this->store->title,
                    'slug' => $this->store->slug,
                ]
                : null,

            'categories' => $this->categories
                ->map(
                    static fn($category): array => [
                        'id' => $category->id,
                        'title' => $category->title,
                        'slug' => $category->slug,
                        'path' => $category->path,
                    ]
                )
                ->values(),

            'pricing' => $pricing,

            'sale_items' => ProductSaleItemResource::collection($this->saleItems),

            'user_access' => [
                'authenticated' => (bool)$this->getAttribute('viewer_authenticated'),
                'is_purchased' => $this->getAttribute('is_purchased'),
                'has_usable_access' => $this->getAttribute('has_usable_access'),
            ],

            'published_at' => $this->published_at?->toISOString(),
        ];
    }
}
