<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildPageSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolvePageSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Content\Models\Page;
use App\Domain\Seo\Enums\OgTypeEnum;

final class ResolvePageSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction        $getSeoDefaultsAction,
        protected ResolvePageSeoPayloadAction $resolvePageSeoPayloadAction,
        protected BuildPageSchemaGraphAction  $buildPageSchemaGraphAction,
        protected RenderSchemaGraphAction     $renderSchemaGraphAction,
    )
    {
    }

    public function execute(Page $page): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolvePageSeoPayloadAction->execute($page);

        $schemaGraph = $this->buildPageSchemaGraphAction->execute($page, $payload);
        $schemaJson = $this->renderSchemaGraphAction->execute($schemaGraph);

        return new SeoDataDTO(
            robots: $payload->robots ?: $defaults['robots'],
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
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
                label1: 'زمان تقریبی برای خواندن',
                data1: $payload->readingTimeLabel,
            ),
            schemaJson: $schemaJson,
        );
    }
}
