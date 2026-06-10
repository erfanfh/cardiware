<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Marketplace\Models\Store;

final class BuildStoreSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(
        Store   $store,
        string  $siteUrl,
        ?string $storeUrl = null,
        ?string $description = null,
        ?array  $logoNode = null,
        ?array  $imageNode = null,
    ): array
    {
        $storeUrl ??= front_route('stores.detail', $store->slug);

        return [
            '@type' => 'Organization',
            '@id' => $this->schemaIdGenerator->store($siteUrl, $store->id),
            'name' => $store->title,
            'url' => $storeUrl,
            'description' => $description ?: $store->body,
            'logo' => $logoNode && isset($logoNode['@id']) ? [
                '@id' => $logoNode['@id'],
            ] : null,
            'image' => $imageNode && isset($imageNode['@id']) ? [
                '@id' => $imageNode['@id'],
            ] : null,
        ];
    }
}
