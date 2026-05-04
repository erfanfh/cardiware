<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\TaxonomySeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Str;

final class ResolvePostTagSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction           $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction      $resolveCanonicalUrlAction,
        protected SeoTemplateParser              $seoTemplateParser,
        protected TaxonomySeoVariableDataBuilder $taxonomySeoVariableDataBuilder,
    )
    {
    }

    public function execute(TaxonomyTerm $tag): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->taxonomySeoVariableDataBuilder->build($tag);

        $titleTemplate = $tag->seoMeta?->title
            ?: get_setting('seo', 'post_tag_seo_title')
                ?: $tag->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $title = $this->appendPaginationToTitle($title);

        $descriptionTemplate = $tag->seoMeta?->description
            ?: get_setting('seo', 'post_tag_seo_description')
                ?: $this->resolveFallbackDescription($tag);

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $tag->seoMeta?->og_title
            ?: get_setting('seo', 'post_tag_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $tag->seoMeta?->og_description
            ?: get_setting('seo', 'post_tag_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $image = $this->resolveFallbackImage($tag);

        $canonicalUrl = $this->resolveCanonicalUrl($tag);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $tag->title,

            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],

            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: $tag->title,

            keywords: [$tag->title],
        );
    }

    protected function resolveFallbackDescription(TaxonomyTerm $tag): string
    {
        $description = (string)($tag->description ?? '');

        if (filled($description)) {
            return Str::limit(trim(strip_tags($description)), 170, '');
        }

        return '';
    }

    protected function resolveFallbackImage(TaxonomyTerm $tag): ?MediaFile
    {
        if (!method_exists($tag, 'posts')) {
            return null;
        }

        $post = $tag->posts()
            ->with(['picture.primaryVariant'])
            ->latest('published_at')
            ->first();

        return $post?->picture;
    }

    protected function resolveCanonicalUrl(TaxonomyTerm $tag): string
    {
        $currentPageUrl = $this->resolveCurrentPageUrl($tag);

        if ($this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $currentPageUrl,
        );
    }

    protected function resolveCurrentPageUrl(TaxonomyTerm $tag): string
    {
        return $this->buildPaginatedUrl(
            baseUrl: front_route('posts.tags', ['path' => $tag->path]),
            page: $this->currentPage(),
        );
    }

    protected function buildPaginatedUrl(string $baseUrl, int $page): string
    {
        $baseUrl = rtrim($baseUrl, '/') . '/';

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . 'page/' . $page . '/';
    }

    protected function currentPage(): int
    {
        return max(1, (int)request()->route('page', 1));
    }

    protected function appendPaginationToTitle(string $title): string
    {
        $page = $this->currentPage();

        if ($page <= 1) {
            return $title;
        }

        $lastPage = request()->attributes->get('seo_paginator')?->lastPage();

        if ($lastPage) {
            return $this->appendBeforeSiteName($title, "صفحه {$page} از {$lastPage}");
        }

        return $this->appendBeforeSiteName($title, "صفحه {$page}");
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
}
