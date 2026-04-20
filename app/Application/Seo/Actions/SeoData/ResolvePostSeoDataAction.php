<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\Actions\Schema\Graphs\BuildPostSchemaGraphAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\Actions\SeoData\Resolvers\ResolvePostSeoPayloadAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Domain\Content\Models\Post;
use App\Domain\Seo\Enums\OgTypeEnum;

final class ResolvePostSeoDataAction
{
    public function __construct(
        protected GetSeoDefaultsAction        $getSeoDefaultsAction,
        protected ResolvePostSeoPayloadAction $resolvePostSeoPayloadAction,
        protected BuildPostSchemaGraphAction  $buildPostSchemaGraphAction,
        protected RenderSchemaGraphAction     $renderSchemaGraphAction,
    )
    {
    }

    public function execute(Post $post): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $payload = $this->resolvePostSeoPayloadAction->execute($post);

        $schemaGraph = $this->buildPostSchemaGraphAction->execute($post, $payload);
        $schemaJson = $this->renderSchemaGraphAction->execute($schemaGraph);

        return new SeoDataDTO(
            robots: $payload->robots ?: $defaults['robots'],
            title: $payload->title,
            description: $payload->description,
            canonicalUrl: $payload->canonicalUrl,
            author: $payload->authorName,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::ARTICLE,
                title: $payload->ogTitle,
                description: $payload->ogDescription,
                url: $payload->canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $payload->ogImage['url'] ?? null,
                imageWidth: $payload->ogImage['width'] ?? null,
                imageHeight: $payload->ogImage['height'] ?? null,
                imageType: $payload->ogImage['type'] ?? null,
                publishedTime: $payload->publishedTime,
                modifiedTime: $payload->modifiedTime,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $payload->xTitle,
                description: $payload->xDescription,
                image: $payload->xImageUrl,
                label1: $payload->authorName ? 'نوشته‌شده بدست' : null,
                data1: $payload->authorName,
                label2: 'زمان تقریبی برای خواندن',
                data2: $payload->readingTimeLabel,
            ),
            schemaJson: $schemaJson,
        );
    }
}
