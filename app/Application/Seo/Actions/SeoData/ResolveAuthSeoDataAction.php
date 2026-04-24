<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildAuthSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveAuthSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;

final class ResolveAuthSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction        $getSeoDefaultsAction,
        protected ResolveAuthSeoPayloadAction $resolveAuthSeoPayloadAction,
        protected BuildAuthSchemaGraphAction  $buildAuthSchemaGraphAction,
        protected RenderSchemaGraphAction     $renderSchemaGraphAction,
    )
    {
    }

    public function execute(): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveAuthSeoPayloadAction->execute();

        $schemaGraph = $this->buildAuthSchemaGraphAction->execute($payload);
        $schemaJson = $this->renderSchemaGraphAction->execute($schemaGraph);

        return new SeoDataDTO(
            robots: $payload->robots,
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
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $payload->xTitle,
                description: $payload->xDescription,
            ),
            schemaJson: $schemaJson,
        );
    }
}
