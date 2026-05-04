<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\AuthorSeoVariableDataBuilder;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Str;

final class ResolveAuthorSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction         $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction    $resolveCanonicalUrlAction,
        protected SeoTemplateParser            $seoTemplateParser,
        protected AuthorSeoVariableDataBuilder $authorSeoVariableDataBuilder,
    )
    {
    }

    public function execute(User $author): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->authorSeoVariableDataBuilder->build($author);

        $authorName = $this->resolveAuthorName($author);
        $siteName = (string)$defaults['site_name'];

        $titleTemplate = '{author_name}، نویسنده در {site_title}';
        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $title = $this->appendPaginationToTitle($title);

        $description = $this->resolveDescription($author, $siteName);

        $ogTitle = $authorName;
        $ogDescription = $description;

        $image = $this->resolvePrimaryImage($author);

        $canonicalUrl = $this->resolveCanonicalUrl($author);

        $sameAs = array_merge([$defaults['site_url']], $this->resolveSameAs($author));

        $authorProfileUrl = $this->resolveAuthorProfileUrl($author);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $title,

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
            pageImageCaption: $authorName,

            personName: $authorName,
            personUrl: $authorProfileUrl,
            sameAs: $sameAs,
        );
    }

    protected function resolveAuthorProfileUrl(User $author): string
    {
        return rtrim(front_route('author', $author), '/') . '/';
    }

    protected function resolveAuthorName(User $author): string
    {
        return (string)(
            $author->name
            ?? $author->display_name
            ?? $author->full_name
            ?? $author->username
            ?? $author->email
            ?? 'نویسنده'
        );
    }

    protected function resolveDescription(User $author, string $siteName): string
    {
        $bio = $author->bio
            ?? $author->about
            ?? $author->description
            ?? null;

        if (filled($bio)) {
            return Str::limit(trim(strip_tags((string)$bio)), 170, '');
        }

        return sprintf('آرشیو نوشته‌های %s در %s', $this->resolveAuthorName($author), $siteName);
    }

    protected function resolvePrimaryImage(User $author): ?MediaFile
    {
        $directImage = $author->avatar ?? null;

        if ($directImage instanceof MediaFile) {
            return $directImage;
        }

        $imageFromCurrentPage = $this->resolveImageFromCurrentPagePosts();

        if ($imageFromCurrentPage) {
            return $imageFromCurrentPage;
        }

        if (method_exists($author, 'posts')) {
            $post = $author->posts()
                ->with(['picture.primaryVariant'])
                ->latest('published_at')
                ->first();

            return $post?->picture;
        }

        return null;
    }

    protected function resolveImageFromCurrentPagePosts(): ?MediaFile
    {
        $paginator = request()->attributes->get('seo_paginator');

        if (!$paginator) {
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

    protected function resolveCanonicalUrl(User $author): string
    {
        $currentPageUrl = $this->resolveCurrentPageUrl($author);

        if ($this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $currentPageUrl,
        );
    }

    protected function resolveCurrentPageUrl(User $author): string
    {
        return $this->buildPaginatedUrl(
            baseUrl: front_route('author', $author),
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

    /**
     * @return array<int, string>
     */
    protected function resolveSameAs(User $author): array
    {
        $candidates = [
            $author->website_url ?? null,
            $author->linkedin_url ?? null,
            $author->twitter_url ?? null,
            $author->github_url ?? null,
            $author->telegram_url ?? null,
        ];

        return array_values(array_filter($candidates, static fn($value) => filled($value)));
    }
}
