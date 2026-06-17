<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildStoreSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveStoreSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Seo\Enums\OgTypeEnum;

final class ResolveStoreSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction         $getSeoDefaultsAction,
        protected ResolveStoreSeoPayloadAction $resolveStoreSeoPayloadAction,
        protected BuildStoreSchemaGraphAction  $buildStoreSchemaGraphAction,
        protected RenderSchemaGraphAction      $renderSchemaGraphAction,
    )
    {
    }

    public function execute(Store $store): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveStoreSeoPayloadAction->execute($store);

        $schemaGraph = $this->buildStoreSchemaGraphAction->execute($store, $payload);
        $schemaJson = $this->renderSchemaGraphAction->execute($schemaGraph);

        return new SeoDataDTO(
            robots: $payload->robots ?: $defaults['robots'],
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
            author: null,

            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: $payload->ogTitle,
                description: $payload->ogDescription,
                url: $payload->canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $payload->ogImage['url'] ?? null,
                imageWidth: $payload->ogImage['width'] ?? null,
                imageHeight: $payload->ogImage['height'] ?? null,
                imageType: $payload->ogImage['type'] ?? null,
            ),

            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $payload->xTitle,
                description: $payload->xDescription,
                image: $payload->xImageUrl,
            ),

            schemaJson: $schemaJson,
        );
    }
}
