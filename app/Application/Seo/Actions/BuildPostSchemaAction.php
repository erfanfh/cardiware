<?php

namespace App\Application\Seo\Actions;

use App\Domain\Content\Models\Post;

class BuildPostSchemaAction
{
    public function __construct(
        private readonly BuildOrganizationSchemaNodeAction $buildOrganizationSchemaNodeAction,
        private readonly BuildWebsiteSchemaNodeAction      $buildWebsiteSchemaNodeAction,
        private readonly GetSeoDefaultsAction              $getSeoDefaultsAction,
    )
    {
    }

    /**
     * @param array{url:?string,width:?int,height:?int,type:?string} $image
     * @return array<int, array<string, mixed>>
     */
    public function execute(
        Post    $post,
        string  $canonicalUrl,
        ?string $title,
        ?string $description,
        array   $image,
        ?string $authorName,
    ): array
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $organization = $this->buildOrganizationSchemaNodeAction->execute();
        $website = $this->buildWebsiteSchemaNodeAction->execute(null);

        $organizationId = $organization['@id'] ?? null;
        $websiteId = $website['@id'] ?? null;

        $breadcrumbId = rtrim($canonicalUrl, '/') . '/#breadcrumb';
        $articleId = rtrim($canonicalUrl, '/') . '/#article';
        $imageId = filled($image['url']) ? rtrim($canonicalUrl, '/') . '/#primaryimage' : null;

        $article = array_filter([
            '@type' => 'Article',
            '@id' => $articleId,
            'isPartOf' => [
                '@id' => $canonicalUrl,
            ],
            'author' => $authorName
                ? [
                    '@type' => 'Person',
                    'name' => $authorName,
                ]
                : null,
            'headline' => $title,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'mainEntityOfPage' => [
                '@id' => $canonicalUrl,
            ],
            'publisher' => $organizationId ? ['@id' => $organizationId] : null,
            'image' => $imageId ? ['@id' => $imageId] : null,
            'thumbnailUrl' => $image['url'] ?? null,
            'inLanguage' => 'fa-IR',
        ], fn($value) => !is_null($value) && $value !== '');

        $webPage = array_filter([
            '@type' => 'WebPage',
            '@id' => $canonicalUrl,
            'url' => $canonicalUrl,
            'name' => $title . ' | ' . $defaults['site_name'],
            'isPartOf' => $websiteId ? ['@id' => $websiteId] : null,
            'primaryImageOfPage' => $imageId ? ['@id' => $imageId] : null,
            'image' => $imageId ? ['@id' => $imageId] : null,
            'thumbnailUrl' => $image['url'] ?? null,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'description' => $description,
            'breadcrumb' => [
                '@id' => $breadcrumbId,
            ],
            'inLanguage' => 'fa-IR',
        ], fn($value) => !is_null($value) && $value !== '');

        $imageObject = null;

        if (filled($image['url'])) {
            $imageObject = array_filter([
                '@type' => 'ImageObject',
                '@id' => $imageId,
                'inLanguage' => 'fa-IR',
                'url' => $image['url'],
                'contentUrl' => $image['url'],
                'width' => $image['width'] ?? null,
                'height' => $image['height'] ?? null,
                'caption' => $title,
            ], fn($value) => !is_null($value) && $value !== '');
        }

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            '@id' => $breadcrumbId,
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'خانه',
                    'item' => $defaults['canonical_home'],
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $title,
                ],
            ],
        ];

        return array_values(array_filter([
            $article,
            $webPage,
            $imageObject,
            $breadcrumb,
            $website,
            $organization,
        ]));
    }
}
