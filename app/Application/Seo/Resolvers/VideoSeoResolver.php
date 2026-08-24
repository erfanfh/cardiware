<?php

namespace App\Application\Seo\Resolvers;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\ResolveSeoPaginationLinksAction;
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
        private readonly GetSeoDefaultsAction            $defaultsAction,
        private readonly ResolveSeoPaginationLinksAction $paginationLinks,
    )
    {
    }

    public function supports(SeoContext $context): bool
    {
        return $context->routeIs('videos.index', 'videos.show', 'videos.categories', 'videos.tags');
    }

    public function resolve(SeoContext $context): SeoDataDTO
    {
        $defaults = $this->defaultsAction->execute();
        $site = $defaults['site_name'];
        $image = $defaults['organization_logo']['url'] ?? null;
        $video = $context->request->attributes->get('video');
        $term = $context->request->attributes->get('video_taxonomy_term');
        $pagination = null;

        if ($video instanceof Video) {
            $meta = $video->seoMeta;
            $fallbackDescription = Str::limit(trim(strip_tags((string)($video->excerpt ?: $video->body))), 160, '…');
            $title = $this->parse($meta?->title ?: '{video_title} | {site_title}', $video->title, $site);
            $description = $this->parse($meta?->description ?: $fallbackDescription, $video->title, $site);
            $url = $meta?->canonical_url ?: $video->url;
            $image = $meta?->ogImage?->url ?: $video->cover?->url ?: $image;
            $schema = array_filter([
                '@context' => 'https://schema.org', '@type' => 'VideoObject',
                'name' => $video->title, 'description' => $description, 'url' => $url,
                'thumbnailUrl' => $image ? [$image] : null,
                'uploadDate' => $video->published_at?->toIso8601String(),
                'duration' => $video->duration_seconds ? $this->isoDuration($video->duration_seconds) : null,
                'contentUrl' => $video->source_type->value === 'direct' ? $video->video_url : null,
                'embedUrl' => $video->embed_url,
            ]);
            $type = OgTypeEnum::ARTICLE;
            $published = $video->published_at?->toIso8601String();
            $modified = $video->updated_at?->toIso8601String();
            $robots = $meta?->robots ?: $defaults['robots'];
            $ogTitle = $this->parse($meta?->og_title ?: $title, $video->title, $site);
            $ogDescription = $this->parse($meta?->og_description ?: $description, $video->title, $site);
        } else {
            $label = $term instanceof TaxonomyTerm ? $term->title : 'مودینگ تی‌وی';
            $title = $label . ' | ' . $site;
            $description = $term instanceof TaxonomyTerm && filled($term->description)
                ? Str::limit(trim(strip_tags($term->description)), 160, '…')
                : 'مرکز ویدیوهای گیمینگ، آموزش مادینگ، تریلر و گیم‌پلی در مودینگ‌وی.';
            $baseUrl = $term instanceof TaxonomyTerm
                ? route($term->taxonomy->archiveRouteName(), $term->path)
                : route('videos.index');
            $paginator = $context->request->attributes->get('seo_paginator');
            $url = $paginator instanceof LengthAwarePaginator && $paginator->currentPage() > 1
                ? $paginator->url($paginator->currentPage())
                : $baseUrl;
            $pagination = $paginator instanceof LengthAwarePaginator
                ? $this->paginationLinks->execute($paginator, $baseUrl, 'query')
                : null;
            $schema = ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $label, 'description' => $description, 'url' => $url];
            $type = OgTypeEnum::WEBSITE;
            $robots = $defaults['robots'];
            $ogTitle = $title;
            $ogDescription = $description;
            $published = $modified = null;
        }

        return new SeoDataDTO(
            robots: $robots, title: $title, description: $description, canonicalUrl: $url,
            openGraph: new OpenGraphDataDTO(type: $type, title: $ogTitle, description: $ogDescription, url: $url, siteName: $site, locale: $defaults['og_locale'], image: $image, publishedTime: $published, modifiedTime: $modified),
            twitter: new TwitterCardDataDTO(card: $defaults['twitter_card'], title: $ogTitle, description: $ogDescription, image: $image),
            schemaJson: json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            pagination: $pagination,
        );
    }

    private function parse(?string $value, string $videoTitle, string $site): string
    {
        return strtr((string)$value, ['{video_title}' => $videoTitle, '{site_title}' => $site, '{separator}' => (string)(get_setting('seo', 'separator') ?: '|')]);
    }

    private function isoDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return 'PT' . ($h ? $h . 'H' : '') . ($m ? $m . 'M' : '') . ($s || (!$h && !$m) ? $s . 'S' : '');
    }
}
