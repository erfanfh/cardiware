<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\DTOs\VerificationMetaDataDTO;

class ResolveHomePageSeoDataAction
{
    public function execute(): SeoDataDTO
    {
        $siteName = get_setting('seo', 'site_name') ?: get_setting('general', 'title') ?: config('app.name');
        $title = get_setting('seo', 'home_title') ?: $siteName;
        $description = get_setting('seo', 'home_description') ?: null;
        $canonicalUrl = rtrim(url('/'), '/') . '/';
        $robots = get_setting('seo', 'robots') ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        $ogImage = get_setting('seo', 'og_image') ?: null;

        return new SeoDataDTO(
            title: $title,
            description: $description,
            robots: $robots,
            canonicalUrl: $canonicalUrl,
            nextUrl: null,
            prevUrl: null,
            openGraph: new OpenGraphDataDTO(
                type: 'website',
                title: $siteName,
                description: $description,
                url: $canonicalUrl,
                siteName: $siteName,
                locale: get_setting('seo', 'og_locale') ?: 'fa_IR',
                image: $ogImage,
            ),
            twitter: new TwitterCardDataDTO(
                card: get_setting('seo', 'twitter_card') ?: 'summary_large_image',
                title: $siteName,
                description: $description,
                image: $ogImage,
            ),
            verification: new VerificationMetaDataDTO(
                googleSiteVerification: get_setting('seo', 'google_site_verification') ?: null,
                bingSiteVerification: get_setting('seo', 'bing_site_verification') ?: null,
            ),
        );
    }
}
