<?php

namespace App\Http\Resources\Api\V1\Library;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LibraryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_id' => $this->id,

            'status' => $this->status->value,
            'is_usable' => $this->isUsableForLauncher(),

            'starts_at' => $this->starts_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),

            'default_download_limit_per_file' => $this->download_limit,

            'last_downloaded_at' => $this->last_downloaded_at?->toISOString(),

            'product' => $this->when(
                $this->relationLoaded('product') && $this->product !== null,
                fn(): array => [
                    'id' => $this->product->id,
                    'title' => $this->product->title,
                    'slug' => $this->product->slug,
                    'picture_id' => $this->product->picture_id,
                ]
            ),

            'sale_item' => $this->when(
                $this->relationLoaded('productSaleItem') && $this->productSaleItem !== null,
                fn(): array => [
                    'id' => $this->productSaleItem->id,
                    'sku' => $this->productSaleItem->sku,
                    'title' => $this->productSaleItem->launcher_title
                        ?: $this->productSaleItem->title,
                    'edition_key' => $this->productSaleItem->edition_key,
                    'version' => $this->productSaleItem->version,
                    'description' => $this->productSaleItem->launcher_excerpt
                        ?: $this->productSaleItem->description,
                ]
            ),

            'files' => $this->when(
                $this->relationLoaded('productSaleItem')
                && $this->productSaleItem !== null
                && $this->productSaleItem->relationLoaded('files'),
                fn() => ProductSaleItemFileResource::collection(
                    $this->productSaleItem->files
                )
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
