<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;
use App\Domain\Seo\Enums\OgTypeEnum;
use App\Infrastructure\Support\ReadingTimeEstimator;

class ResolvePostSeoDataAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
    )
    {
    }

    public function execute(Post $post): SeoDataDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $data = app(PostSeoVariableDataBuilder::class)->build($post);

        $titleTemplate = $post->seoMeta?->title ?: get_setting('seo', 'post_seo_title') ?: $post->title;
        $title = app(SeoTemplateParser::class)->parse($titleTemplate, $data);

        $ogTitleTemplate = $post->seoMeta?->og_title ?: get_setting('seo', 'post_social_seo_title') ?: $title;
        $ogTitle = app(SeoTemplateParser::class)->parse($ogTitleTemplate, $data);

        $xTitleTemplate = $post->seoMeta?->x_title;
        $xTitle = $xTitleTemplate ? app(SeoTemplateParser::class)->parse($xTitleTemplate, $data) : null;

        $descriptionTemplate = $post->seoMeta?->description ?: get_setting('seo', 'post_seo_description') ?: $post->excerpt();
        $description = app(SeoTemplateParser::class)->parse($descriptionTemplate, $data);

        $ogDescriptionTemplate = $post->seoMeta?->og_description ?: get_setting('seo', 'post_social_seo_description') ?: $description;
        $ogDescription = app(SeoTemplateParser::class)->parse($ogDescriptionTemplate, $data);

        $xDescriptionTemplate = $post->seoMeta?->x_description;
        $xDescription = $xDescriptionTemplate ? app(SeoTemplateParser::class)->parse($xDescriptionTemplate, $data) : null;

        $ogImage = $post->seoMeta?->ogImage ?: $post->picture;
        $ogImageData = [
            'url' => $ogImage?->url,
            'width' => $ogImage?->primaryVariant?->width,
            'height' => $ogImage?->primaryVariant?->height,
            'type' => $ogImage?->primaryVariant?->mime_type,
        ];

        $xImageUrl = $post->seoMeta?->xImage?->url;

        $canonicalUrl = rtrim($post->url, '/') . '/';

        $publishedTime = $post->published_at?->utc()->toIso8601String();
        $modifiedTime = $post->updated_at > $post->published_at ? $post->updated_at?->utc()->toIso8601String() : $publishedTime;

        $author = $post->author?->display_name;

        return new SeoDataDTO(
            title: $title,
            description: $description,
            canonicalUrl: $canonicalUrl,
            author: $author,
            openGraph: new OpenGraphDataDTO(
                type: OgTypeEnum::ARTICLE,
                title: $ogTitle,
                description: $ogDescription,
                url: $canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
                image: $ogImageData['url'],
                imageWidth: $ogImageData['width'],
                imageHeight: $ogImageData['height'],
                imageType: $ogImageData['type'],
                publishedTime: $publishedTime,
                modifiedTime: $modifiedTime,
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
                title: $xTitle,
                description: $xDescription,
                image: $xImageUrl,
                label1: $author ? 'نوشته‌شده بدست' : null,
                data1: $author,
                label2: 'زمان تقریبی برای خواندن',
                data2: app(ReadingTimeEstimator::class)->estimate($post->body) . ' دقیقه',
            ),
        );
    }
}
