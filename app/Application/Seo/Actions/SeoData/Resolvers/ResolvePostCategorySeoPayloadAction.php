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

final class ResolvePostCategorySeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction           $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction      $resolveCanonicalUrlAction,
        protected SeoTemplateParser              $seoTemplateParser,
        protected TaxonomySeoVariableDataBuilder $taxonomySeoVariableDataBuilder,
    )
    {
    }

    public function execute(TaxonomyTerm $category): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->taxonomySeoVariableDataBuilder->build($category);

        $titleTemplate = $category->seoMeta?->title
            ?: get_setting('seo', 'post_category_seo_title')
                ?: $category->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $title = $this->appendPaginationToTitle($title);

        $descriptionTemplate = $category->seoMeta?->description
            ?: get_setting('seo', 'post_category_seo_description')
                ?: $this->resolveFallbackDescription($category);

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $category->seoMeta?->og_title
            ?: get_setting('seo', 'post_category_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $category->seoMeta?->og_description
            ?: get_setting('seo', 'post_category_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $image = $this->resolveFallbackImage($category);

        $canonicalUrl = $this->resolveCanonicalUrl($category);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $category->title,

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
            pageImageCaption: $category->title,

            keywords: [$category->title],
        );
    }

    protected function resolveFallbackDescription(TaxonomyTerm $category): string
    {
        $description = (string)($category->description ?? '');

        if (filled($description)) {
            return Str::limit(trim(strip_tags($description)), 170, '');
        }

        return '';
    }

    protected function resolveFallbackImage(TaxonomyTerm $category): ?MediaFile
    {
        if (!method_exists($category, 'posts')) {
            return null;
        }

        $post = $category->posts()
            ->with(['picture.primaryVariant'])
            ->latest('published_at')
            ->first();

        return $post?->picture;
    }

    protected function resolveCurrentPageUrl(TaxonomyTerm $category): string
    {
        return $this->buildPaginatedUrl(
            baseUrl: front_route('posts.categories', ['path' => $category->path]),
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

    protected function resolveCanonicalUrl(TaxonomyTerm $category): string
    {
        $currentPageUrl = $this->resolveCurrentPageUrl($category);

        if ($this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $currentPageUrl,
        );
    }

    protected function currentPage(): int
    {
        return max(1, (int)request()->route('page', 1));
    }
}
