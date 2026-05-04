<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\SearchSeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;

final class ResolveSearchSeoPayloadAction
{
    public function __construct(
        protected ResolveCanonicalUrlAction    $resolveCanonicalUrlAction,
        protected SeoTemplateParser            $seoTemplateParser,
        protected SearchSeoVariableDataBuilder $searchSeoVariableDataBuilder,
    )
    {
    }

    public function execute(): ResolvedSeoPayloadDTO
    {
        $searchTerm = $this->resolveSearchTerm();
        $variables = $this->searchSeoVariableDataBuilder->build($searchTerm);

        $titleTemplate = get_setting('seo', 'search_seo_title')
            ?: 'شما برای {search_term} جستجو کردید {separator} {site_title}';

        $descriptionTemplate = get_setting('seo', 'search_seo_description')
            ?: 'نتایج جستجو برای {search_term} در {site_title} را مشاهده کنید.';

        $title = $this->appendPaginationToTitle(
            $this->seoTemplateParser->parse($titleTemplate, $variables)
        );

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $image = $this->resolvePrimaryImage();
        $canonicalUrl = $this->resolveCanonicalUrl($searchTerm);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: 'noindex, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',

            title: $title,
            description: $description,
            headline: filled($searchTerm)
                ? 'نتایج جستجو برای ' . $searchTerm
                : 'نتایج جستجو',

            ogTitle: $title,
            ogDescription: $description,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],

            xTitle: $title,
            xDescription: $description,
            xImageUrl: $image?->url,

            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: filled($searchTerm)
                ? 'نتایج جستجو برای ' . $searchTerm
                : 'نتایج جستجو',
        );
    }

    protected function resolveSearchTerm(): string
    {
        return trim((string)request()->query('q', request()->query('s', '')));
    }

    protected function resolveCanonicalUrl(string $searchTerm): string
    {
        $currentPageUrl = $this->resolveCurrentPageUrl($searchTerm);

        if (filled($searchTerm) || $this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $currentPageUrl,
        );
    }

    protected function resolveCurrentPageUrl(string $searchTerm): string
    {
        $query = [];

        if (filled($searchTerm)) {
            $query['q'] = $searchTerm;
        }

        if ($this->currentPage() > 1) {
            $query['page'] = $this->currentPage();
        }

        return front_route('search', $query);
    }

    protected function currentPage(): int
    {
        return max(1, (int)request()->integer('page', 1));
    }

    protected function appendPaginationToTitle(string $title): string
    {
        $page = $this->currentPage();

        if ($page <= 1) {
            return $title;
        }

        $lastPage = request()->attributes->get('seo_paginator')?->lastPage();

        return $this->appendBeforeSiteName(
            $title,
            $lastPage ? "صفحه {$page} از {$lastPage}" : "صفحه {$page}"
        );
    }

    protected function appendBeforeSiteName(string $title, string $paginationText): string
    {
        $separator = ' | ';
        $parts = explode($separator, $title);

        if (count($parts) >= 2) {
            $siteName = array_pop($parts);

            return implode($separator, $parts)
                . $separator
                . $paginationText
                . $separator
                . $siteName;
        }

        return $title . $separator . $paginationText;
    }

    protected function resolvePrimaryImage(): ?MediaFile
    {
        $paginator = request()->attributes->get('seo_paginator');

        if (!$paginator || !method_exists($paginator, 'items')) {
            return null;
        }

        foreach ($paginator->items() as $post) {
            if ($post->relationLoaded('picture') && $post->picture instanceof MediaFile) {
                return $post->picture;
            }

            if ($post->picture instanceof MediaFile) {
                return $post->picture;
            }
        }

        return null;
    }
}
