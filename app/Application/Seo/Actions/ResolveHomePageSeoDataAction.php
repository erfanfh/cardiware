<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\DTOs\VerificationMetaDataDTO;

class ResolveHomePageSeoDataAction
{
    public function __construct(
        private readonly BuildHomePageSchemaAction $buildHomePageSchemaAction,
        private readonly GetSeoDefaultsAction      $getSeoDefaultsAction,
    )
    {
    }

    public function execute(): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $title = get_setting('seo', 'home_title') ?: $defaults['site_name'];
        $description = get_setting('seo', 'home_description') ?: null;
        $canonicalUrl = $defaults['canonical_home'];

        $schemaGraph = $this->buildHomePageSchemaAction->execute(
            canonicalUrl: $canonicalUrl,
            title: $title,
            description: $description,
        );

        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $schemaGraph,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return new SeoDataDTO(
            title: $title,
            description: $description,
            robots: $defaults['robots'],
            canonicalUrl: $canonicalUrl,
            schemaJson: $schemaJson ?: null,
            openGraph: new OpenGraphDataDTO(
                type: 'website',
                title: $defaults['site_name'],
                description: $description,
                url: $canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $defaults['og_image'],
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $defaults['site_name'],
                description: $description,
                image: $defaults['og_image'],
            ),
            verification: new VerificationMetaDataDTO(
                googleSiteVerification: get_setting('seo', 'google_site_verification') ?: null,
                bingSiteVerification: get_setting('seo', 'bing_site_verification') ?: null,
            ),
        );
    }
}
