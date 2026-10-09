<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Discussion\DTOs\CommentReviewDTO;
use App\Application\Seo\DTOs\ProductSchemaContextDTO;

final class BuildProductSchemaAction
{
    public function __construct(
        protected BuildProductOfferSchemaAction          $buildProductOfferSchemaAction,
        protected BuildProductAggregateOfferSchemaAction $buildProductAggregateOfferSchemaAction,
        protected BuildAggregateRatingSchemaAction       $buildAggregateRatingSchemaAction,
        protected BuildReviewSchemaAction                $buildReviewSchemaAction,
    )
    {
    }

    /**
     * @param array<string, mixed> $primaryImageNode
     * @return array<string, mixed>
     */
    public function execute(
        ProductSchemaContextDTO $context,
        array                   $primaryImageNode = [],
    ): array
    {
        $primaryImageId = $primaryImageNode['@id'] ?? null;

        $offers = $context->hasMultipleOffers()
            ? $this->buildProductAggregateOfferSchemaAction->execute($context)
            : $this->buildProductOfferSchemaAction->execute($context);

        return [
            '@type' => 'Product',
            '@id' => $context->base->pageUrl . '#product',
            'name' => $context->productName,
            'description' => $context->productDescription ?: $context->base->pageDescription,
            'url' => $context->base->pageUrl,
            'image' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : $context->base->pageImageUrl,
            'sku' => $context->sku,
            'category' => $context->categoryName,
            'keywords' => $context->keywords,
            'mainEntityOfPage' => [
                '@id' => $context->base->pageUrl,
            ],
            'aggregateRating' => $this->buildAggregateRatingSchemaAction->execute($context->base),
            'review' => array_map(
                fn (CommentReviewDTO $review): array => $this->buildReviewSchemaAction->execute($review, $context->base->pageUrl),
                $context->reviews,
            ),
            'offers' => $offers,
            'additionalProperty' => $context->additionalProperties,
        ];
    }
}
