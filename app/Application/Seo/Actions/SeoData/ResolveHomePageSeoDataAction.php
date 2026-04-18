<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;

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
            title: $title,
            description: $description,
            canonicalUrl: $canonicalUrl,
            openGraph: new OpenGraphDataDTO(
                locale: $defaults['og_locale'],
            )
        );
    }
}
