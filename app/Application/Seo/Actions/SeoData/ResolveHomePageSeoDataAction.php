<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\DTOs\VerificationMetaDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;

class ResolveHomePageSeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
    )
    {
    }

    public function execute(): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $title = get_setting('seo', 'home_title') ?: $defaults['site_name'];
        $description = get_setting('seo', 'home_description') ?: null;
        $canonicalUrl = rtrim(url('/'), '/') . '/';

        return new SeoDataDTO(
            robots: $defaults['robots'],
            title: $title,
            description: $description,
            canonicalUrl: $canonicalUrl,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: $defaults['site_name'],
                description: $description,
                url: $canonicalUrl,
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
        );
    }
}
