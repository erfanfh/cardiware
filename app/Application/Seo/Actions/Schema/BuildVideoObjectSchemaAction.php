<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Video\Enums\VideoSourceTypeEnum;
use App\Domain\Video\Models\Video;

final class BuildVideoObjectSchemaAction
{
    public function __construct(private readonly SchemaIdGenerator $ids) {}

    /**
     * @param  array<string, mixed>  $organizationNode
     * @param  array<string, mixed>  $personNode
     * @param  array<string, mixed>  $imageNode
     * @return array<string, mixed>
     */
    public function execute(
        Video $video,
        SchemaContextDTO $context,
        array $organizationNode = [],
        array $personNode = [],
        array $imageNode = [],
    ): array {
        $contentUrl = $video->source_type === VideoSourceTypeEnum::DIRECT
            ? $this->httpUrl($video->video_url)
            : null;
        $embedUrl = $video->source_type === VideoSourceTypeEnum::APARAT
            ? $this->httpUrl($video->embed_url)
            : null;

        // A VideoObject without these properties cannot represent a real watch page
        // and is not eligible for Google's video rich results.
        if (blank($context->headline) || blank($context->pageImageUrl) || blank($context->publishedTime) || (! $contentUrl && ! $embedUrl)) {
            return [];
        }

        $videoId = $this->ids->video($context->pageUrl);
        $publisherId = $organizationNode['@id'] ?? null;
        $uploaderId = $personNode['@id'] ?? $publisherId;
        $imageId = $imageNode['@id'] ?? null;
        $keywords = array_values(array_unique(array_filter([
            ...$context->articleSections,
            ...$context->keywords,
        ])));

        return [
            '@type' => 'VideoObject',
            '@id' => $videoId,
            'name' => $context->headline,
            'headline' => $context->headline,
            'description' => $context->pageDescription,
            'url' => $context->pageUrl,
            'mainEntityOfPage' => ['@id' => $context->pageUrl],
            'thumbnailUrl' => [$context->pageImageUrl],
            'thumbnail' => $imageId ? ['@id' => $imageId] : null,
            'image' => $imageId ? ['@id' => $imageId] : $context->pageImageUrl,
            'uploadDate' => $context->publishedTime,
            'datePublished' => $context->publishedTime,
            'dateModified' => $context->modifiedTime,
            'duration' => $video->duration_seconds > 0 ? $this->isoDuration($video->duration_seconds) : null,
            'contentUrl' => $contentUrl,
            'embedUrl' => $embedUrl,
            'inLanguage' => $context->inLanguage,
            'isAccessibleForFree' => true,
            'publisher' => $publisherId ? ['@id' => $publisherId] : null,
            'author' => $uploaderId ? ['@id' => $uploaderId] : null,
            'uploader' => $uploaderId ? ['@id' => $uploaderId] : null,
            'genre' => $context->articleSections,
            'keywords' => $keywords,
            'interactionStatistic' => [
                '@type' => 'InteractionCounter',
                'interactionType' => ['@type' => 'WatchAction'],
                'userInteractionCount' => public_page_views($video),
            ],
            'potentialAction' => [
                '@type' => 'WatchAction',
                'target' => $context->pageUrl,
            ],
        ];
    }

    private function httpUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        return filter_var($url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $url)
            ? $url
            : null;
    }

    private function isoDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $seconds %= 60;

        return 'PT'
            .($hours ? $hours.'H' : '')
            .($minutes ? $minutes.'M' : '')
            .($seconds || (! $hours && ! $minutes) ? $seconds.'S' : '');
    }
}
