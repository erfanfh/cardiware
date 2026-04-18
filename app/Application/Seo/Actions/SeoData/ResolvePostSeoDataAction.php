<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;

class ResolvePostSeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
    )
    {
    }

    public function execute(): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        return new SeoDataDTO(
            openGraph: new OpenGraphDataDTO(
                locale: $defaults['og_locale'],
            )
        );
    }
}
