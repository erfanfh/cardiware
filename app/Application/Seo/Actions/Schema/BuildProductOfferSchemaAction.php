<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\ProductSchemaContextDTO;

final class BuildProductOfferSchemaAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(ProductSchemaContextDTO $context): array
    {
        return [
            '@type' => 'Offer',
            '@id' => $context->base->pageUrl . '#offer',
            'url' => $context->base->pageUrl,
            'sku' => $context->sku,
            'priceCurrency' => $context->priceCurrency,
            'price' => $context->price ?? 0,
            'availability' => $context->isAvailable
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => $context->sellerSchemaId ? [
                '@id' => $context->sellerSchemaId,
            ] : null,
        ];
    }
}
