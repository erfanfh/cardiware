<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Content\Models\Post;

class ResolvePostSeoDataAction
{
    public function __construct(
        private readonly BuildPostSchemaAction $buildPostSchemaAction,
        private readonly GetSeoDefaultsAction  $getSeoDefaultsAction,
    )
    {
    }

    public function execute(Post $post): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $seoMeta = $post->seoMeta;

        $title = $seoMeta?->title ?: $post->title;
        $description = $seoMeta?->description ?: $this->resolveDescription($post);
        $canonicalUrl = $seoMeta?->canonical_url ?: $this->resolveCanonicalUrl($post);
        $robots = $seoMeta?->robots ?: $defaults['robots'];

        $ogTitle = $seoMeta?->og_title ?: $title;
        $ogDescription = $seoMeta?->og_description ?: $description;

        $image = $this->resolveImage($post, $seoMeta?->og_image, $defaults['og_image']);
        $authorName = $this->resolveAuthorName($post);

        $schemaGraph = $this->buildPostSchemaAction->execute(
            post: $post,
            canonicalUrl: $canonicalUrl,
            title: $title,
            description: $description,
            image: $image,
            authorName: $authorName,
        );

        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $schemaGraph,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return new SeoDataDTO(
            title: $title . ' | ' . $defaults['site_name'],
            description: $description,
            robots: $robots,
            canonicalUrl: $canonicalUrl,
            author: $authorName,
            schemaJson: $schemaJson ?: null,
            openGraph: new OpenGraphDataDTO(
                type: 'article',
                title: $ogTitle,
                description: $ogDescription,
                url: $canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $image['url'] ?? null,
                imageWidth: $image['width'] ?? null,
                imageHeight: $image['height'] ?? null,
                imageType: $image['type'] ?? null,
                publishedTime: $post->published_at?->toAtomString(),
                modifiedTime: $post->updated_at?->toAtomString(),
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $ogTitle,
                description: $ogDescription,
                image: $image['url'] ?? null,
                label1: $authorName ? 'نوشته‌شده بدست' : null,
                data1: $authorName,
                label2: 'زمان تقریبی برای خواندن',
                data2: $this->estimateReadingTime($post),
            ),
            verification: null,
        );
    }

    private function resolveDescription(Post $post): ?string
    {
        $raw = $post->excerpt
            ?? $post->summary
            ?? strip_tags((string)$post->content);

        $raw = preg_replace('/\s+/u', ' ', trim((string)$raw));

        return filled($raw) ? mb_substr($raw, 0, 170) : null;
    }

    private function resolveCanonicalUrl(Post $post): string
    {
        return route('contents.detail', $post->slug);
    }

    /**
     * @return array{url:?string,width:?int,height:?int,type:?string}
     */
    private function resolveImage(Post $post, ?string $seoImage, ?string $defaultOgImage): array
    {
        if (filled($seoImage)) {
            return [
                'url' => $seoImage,
                'width' => null,
                'height' => null,
                'type' => null,
            ];
        }

        if (method_exists($post, 'getSeoImageData')) {
            return $post->getSeoImageData();
        }

        if (filled($defaultOgImage)) {
            return [
                'url' => $defaultOgImage,
                'width' => null,
                'height' => null,
                'type' => null,
            ];
        }

        return [
            'url' => null,
            'width' => null,
            'height' => null,
            'type' => null,
        ];
    }

    private function resolveAuthorName(Post $post): ?string
    {
        return $post->author?->name
            ?? $post->user?->name
            ?? null;
    }

    private function estimateReadingTime(Post $post): string
    {
        $text = strip_tags((string)($post->content ?? ''));
        $text = preg_replace('/\s+/u', ' ', trim($text));
        $wordCount = str_word_count($text);

        $minutes = max(1, (int)ceil($wordCount / 200));

        return $minutes . ' دقیقه';
    }
}
