<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedHomePageSeoPayloadDTO;

final class ResolveHomePageSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction      $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction $resolveCanonicalUrlAction,
    )
    {
    }

    public function execute(): ResolvedHomePageSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: url('/'),
        );

        $title = get_setting('seo', 'home_title') ?: $defaults['site_name'];
        $description = get_setting('seo', 'home_description') ?: null;

        return new ResolvedHomePageSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],
            title: $title,
            description: $description,
            ogTitle: $defaults['site_name'],
            ogDescription: $description,
        );
    }
}
