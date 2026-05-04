<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostListSeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;

final class ResolvePostListSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction           $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction      $resolveCanonicalUrlAction,
        protected SeoTemplateParser              $seoTemplateParser,
        protected PostListSeoVariableDataBuilder $postListSeoVariableDataBuilder,
    )
    {
    }

    public function execute(): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->postListSeoVariableDataBuilder->build();

        $titleTemplate = get_setting('seo', 'post_list_seo_title')
            ?: 'نوشته ها {separator} {site_title}';

        $descriptionTemplate = get_setting('seo', 'post_list_seo_description')
            ?: 'جدیدترین نوشته‌ها، آموزش‌ها، اخبار و مطالب منتشر شده در {site_title} را بخوانید.';

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $title = $this->appendPaginationToTitle($title);

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $image = $this->resolvePrimaryImage();

        $canonicalUrl = $this->resolveCanonicalUrl();

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: 'نوشته‌ها',

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
            pageImageCaption: 'نوشته‌ها',
        );
    }

    protected function resolveCanonicalUrl(): string
    {
        $currentPageUrl = $this->resolveCurrentPageUrl();

        if ($this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $currentPageUrl,
        );
    }

    protected function resolveCurrentPageUrl(): string
    {
        return $this->buildPaginatedUrl(
            baseUrl: front_route('posts.list'),
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

    protected function currentPage(): int
    {
        return max(1, (int)request()->route('page', 1));
    }

    protected function resolvePrimaryImage(): ?MediaFile
    {
        $paginator = request()->attributes->get('seo_paginator');

        if (!$paginator) {
            return null;
        }

        foreach ($paginator->items() as $post) {
            if ($post->picture instanceof MediaFile) {
                return $post->picture;
            }
        }

        return null;
    }
}
