<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
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

        return new SeoDataDTO(
            title: $title,
        );
    }
}
