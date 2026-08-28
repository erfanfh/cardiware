<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\ResolveSeoPaginationLinksAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildVideoCollectionSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\ResolveVideoSeoDataAction;
use App\Application\Seo\Contracts\SeoResolverInterface;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\Support\SeoContext;
use App\Domain\Seo\Enums\OgTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final class VideoSeoResolver implements SeoResolverInterface
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaultsAction,
        private readonly ResolveSeoPaginationLinksAction $paginationLinks,
        private readonly ResolveVideoSeoDataAction $videoSeoDataAction,
        private readonly BuildVideoCollectionSchemaGraphAction $collectionGraphAction,
        private readonly RenderSchemaGraphAction $renderSchemaGraphAction,
    ) {}

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('videos.index', 'videos.show', 'videos.categories', 'videos.tags');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $video = $context->request->attributes->get('video');

        return $video instanceof Video
            ? $this->videoSeoDataAction->execute($video)
            : $this->resolveCollection($context);
    }

    private function resolveCollection(SeoContext $context): SeoDataDTO
    {
        $defaults = $this->defaultsAction->execute();
        $term = $context->request->attributes->get('video_taxonomy_term');
        $term = $term instanceof TaxonomyTerm ? $term : null;
        $meta = $term?->seoMeta;
        $label = $term?->title ?: 'مودینگ تی‌وی';
        $title = $meta?->title ?: $label.' | '.$defaults['site_name'];
        $description = $meta?->description ?: ($term && filled($term->description)
            ? Str::limit(trim(strip_tags($term->description)), 160, '…')
            : 'مرکز ویدیوهای گیمینگ، آموزش مادینگ، تریلر و گیم‌پلی در مودینگ‌وی.');
        $baseUrl = $term
            ? route($term->taxonomy->archiveRouteName(), $term->path)
            : route('videos.index');
        $paginator = $context->request->attributes->get('seo_paginator');
        $paginator = $paginator instanceof LengthAwarePaginator ? $paginator : null;
        $url = $paginator && $paginator->currentPage() > 1
            ? $baseUrl.(str_contains($baseUrl, '?') ? '&' : '?').'page='.$paginator->currentPage()
            : ($meta?->canonical_url ?: $baseUrl);
        $image = $meta?->ogImage?->url ?: $defaults['organization_logo']?->url;
        $ogTitle = $meta?->og_title ?: $title;
        $ogDescription = $meta?->og_description ?: $description;

        return new SeoDataDTO(
            robots: $meta?->robots ?: $defaults['robots'],
            title: $title,
            description: $description,
            canonicalUrl: $url,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::WEBSITE,
                title: $ogTitle,
                description: $ogDescription,
                url: $url,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $image,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $meta?->x_title ?: $ogTitle,
                description: $meta?->x_description ?: $ogDescription,
                image: $meta?->xImage?->url ?: $image,
            ),
            schemaJson: $this->renderSchemaGraphAction->execute(
                $this->collectionGraphAction->execute($url, $title, $description, $paginator, $term)
            ),
            pagination: $paginator ? $this->paginationLinks->execute($paginator, $baseUrl, 'query') : null,
        );
    }
}
