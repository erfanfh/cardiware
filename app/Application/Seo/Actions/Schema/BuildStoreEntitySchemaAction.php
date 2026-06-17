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
     * @param array<string, mixed> $organizationNode
     * @param array<string, mixed> $primaryImageNode
     */
    public function execute(
        Store            $store,
        SchemaContextDTO $context,
        array            $organizationNode = [],
        array            $primaryImageNode = [],
    ): array
    {

        $primaryImageId = $primaryImageNode['@id'] ?? null;
        $organizationId = $organizationNode['@id'] ?? null;

        $storeId = $this->schemaIdGenerator->custom($context->pageUrl, '#store');

        return [
            '@type' => 'Organization',
            '@id' => $storeId,

            'name' => $store->title,
            'url' => $context->pageUrl,
            'description' => $context->pageDescription,

            /**
             * Image (preferred: real picture)
             */
            'image' => $store->picture?->url
                ? $store->picture->url
                : ($primaryImageId ? ['@id' => $primaryImageId] : null),

            /**
             * IMPORTANT: link to main site org
             */
            'parentOrganization' => $organizationId
                ? ['@id' => $organizationId]
                : null,

            'keywords' => $context->keywords,

            /**
             * Optional rating
             */
            'aggregateRating' => (isset($store->rating_avg) && isset($store->reviews_count))
                ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $store->rating_avg,
                    'reviewCount' => $store->reviews_count,
                ]
                : null,
        ];
    }
}
