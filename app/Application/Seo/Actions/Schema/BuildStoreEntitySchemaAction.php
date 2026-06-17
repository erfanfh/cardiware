<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Marketplace\Models\Store;

final class BuildStoreEntitySchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $primaryImageNode
     */
    public function execute(
        Store            $store,
        SchemaContextDTO $context,
        array            $primaryImageNode = [],
    ): array
    {

        $primaryImageId = $primaryImageNode['@id'] ?? null;

        $storeId = $this->schemaIdGenerator->custom($context->pageUrl, '#store');

        $node = [
            '@type' => 'Organization',
            '@id' => $storeId,

            'name' => $store->title,
            'url' => $context->pageUrl,
            'description' => $context->pageDescription,

            'image' => $primaryImageId
                ? ['@id' => $primaryImageId]
                : $context->pageImageUrl,

            'keywords' => $context->keywords,
        ];

        /**
         * Optional rating (only if exists)
         */
        if (isset($store->rating_avg) && isset($store->reviews_count)) {
            $node['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $store->rating_avg,
                'reviewCount' => $store->reviews_count,
            ];
        }

        return $node;
    }
}
