<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\ResolveSeoPaginationLinksAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildSearchSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolveSearchSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\SeoPaginationLinksDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Seo\Enums\OgTypeEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ResolveSearchSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction            $getSeoDefaultsAction,
        protected ResolveSearchSeoPayloadAction   $resolveSearchSeoPayloadAction,
        protected BuildSearchSchemaGraphAction    $buildSearchSchemaGraphAction,
        protected RenderSchemaGraphAction         $renderSchemaGraphAction,
        protected ResolveSeoPaginationLinksAction $resolveSeoPaginationLinksAction,
    )
    {
    }

    public function execute(): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolveSearchSeoPayloadAction->execute();

        $schemaGraph = $this->buildSearchSchemaGraphAction->execute($payload);
        $schemaJson = $this->renderSchemaGraphAction->execute($schemaGraph);

        return new SeoDataDTO(
            robots: $payload->robots ?: $defaults['robots'],
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
            author: null,
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
            ),
            schemaJson: $schemaJson,
            pagination: $this->resolvePagination($payload->canonicalUrl),
        );
    }

    protected function resolvePagination(?string $canonicalUrl): ?SeoPaginationLinksDTO
    {
        if (blank($canonicalUrl)) {
            return null;
        }

        $paginator = request()->attributes->get('seo_paginator');

        if (!$paginator instanceof LengthAwarePaginator) {
            return null;
        }

        $pagination = $this->resolveSeoPaginationLinksAction->execute(
            paginator: $paginator,
            baseUrl: $canonicalUrl,
            mode: 'query',
        );

        return $pagination->hasAny() ? $pagination : null;
    }
}
