<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\ProductSchemaContextDTO;

final class BuildProductAggregateOfferSchemaAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(ProductSchemaContextDTO $context): array
    {
        return [
            '@type' => 'AggregateOffer',
            '@id' => $context->base->pageUrl . '#aggregate-offer',
            'url' => $context->base->pageUrl,
            'priceCurrency' => $context->priceCurrency,
            'lowPrice' => $context->lowPrice ?? 0,
            'highPrice' => $context->highPrice ?? $context->lowPrice ?? 0,
            'offerCount' => max($context->offerCount, count($context->offers)),
            'offers' => $context->offers,
        ];
    }
}
