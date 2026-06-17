<?php

namespace App\Application\Seo\Actions\Schema\Contexts;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Domain\Marketplace\Models\Store;

final class BuildStoreSchemaContextAction
{
    public function __construct(
        protected GetSeoDefaultsAction $getSeoDefaultsAction,
    )
    {
    }

    public function execute(Store $store, ResolvedSeoPayloadDTO $payload): SchemaContextDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $siteName = $defaults['site_name'] ?? null;
        $siteUrl = $defaults['site_url'] ?? null;

        return new SchemaContextDTO(
            pageUrl: $payload->canonicalUrl,
            pageTitle: $payload->title,
            pageDescription: $payload->description,
            pageType: 'WebPage',

            siteUrl: $siteUrl,
            siteName: (string)$siteName,
            siteAlternateName: get_setting('seo', 'site_alternate_name'),
            siteDescription: get_setting('seo', 'site_description'),
            inLanguage: (string)get_setting('seo', 'schema_language', 'fa-IR'),

            /**
             * Store image only
             */
            pageImageUrl: $store->picture?->url,
            pageImageWidth: null,
            pageImageHeight: null,
            pageImageCaption: $store->title,

            headline: $store->title,
            publishedTime: $payload->publishedTime,
            modifiedTime: $payload->modifiedTime,

            keywords: $payload->keywords,

            authorName: null,
            authorUrl: null,
            authorIdentifier: null,
            authorSameAs: [],
        );
    }
}
