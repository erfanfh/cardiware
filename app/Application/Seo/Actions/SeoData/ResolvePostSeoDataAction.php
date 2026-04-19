<?php

namespace App\Application\Seo\Actions\SeoData;

use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\OpenGraphDataDTO;
use App\Application\Seo\DTOs\SeoDataDTO;
use App\Application\Seo\DTOs\TwitterCardDataDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;

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

        $descriptionTemplate = $post->seoMeta?->description ?: get_setting('seo', 'post_seo_description') ?: $post->excerpt();
        $description = app(SeoTemplateParser::class)->parse($descriptionTemplate, $data);

        $ogDescriptionTemplate = $post->seoMeta?->og_description ?: get_setting('seo', 'post_social_seo_description') ?: $description;
        $ogDescription = app(SeoTemplateParser::class)->parse($ogDescriptionTemplate, $data);

        $canonicalUrl = rtrim($post->url, '/') . '/';

        return new SeoDataDTO(
            title: $title,
            description: $description,
            canonicalUrl: $canonicalUrl,
            openGraph: new OpenGraphDataDTO(
                title: $ogTitle,
                description: $ogDescription,
                url: $canonicalUrl,
                siteName: $defaults['site_name'],
                locale: $defaults['og_locale'],
            ),
            twitter: new TwitterCardDataDTO(
                card: $defaults['twitter_card'],
            ),
        );
    }
}
