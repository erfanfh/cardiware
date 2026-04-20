<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildHomePageSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveHomePageSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\DTOs\VerificationMetaDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;

final class ResolveHomePageSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction            $getSeoDefaultsAction,
        protected ResolveHomePageSeoPayloadAction $resolveHomePageSeoPayloadAction,
        protected BuildHomePageSchemaGraphAction  $buildHomePageSchemaGraphAction,
        protected RenderSchemaGraphAction         $renderSchemaGraphAction,
    )
    {
    }

    public function execute(): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveHomePageSeoPayloadAction->execute();

        $schemaGraph = $this->buildHomePageSchemaGraphAction->execute($payload);
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
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
            ),
            verification: new VerificationMetaDataDTO(
                googleSiteVerification: get_setting('seo', 'google_site_verification') ?: null,
                bingSiteVerification: get_setting('seo', 'bing_site_verification') ?: null,
            ),
            schemaJson: $schemaJson,
        );
    }
}
