<?php

namespace App\Application\Seo\Actions\Boxes;

use App\Application\Seo\Actions\Analysis\BuildSeoAnalysisViewDataAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Content\Models\Post;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

final class BuildPostSeoBoxConfigAction
{
    public function __construct(
        protected GetSeoDefaultsAction           $getSeoDefaultsAction,
        protected BuildSeoAnalysisViewDataAction $buildSeoAnalysisViewDataAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(?Post $post): array
    {
        $siteTitle = $this->getSeoDefaultsAction->execute()['site_name'];

        return [
            'entityType' => 'post',
            'defaultTab' => 'general',
            'primaryTitleToken' => SeoTemplateVariableEnum::POST_TITLE->token(),
            'showFocusKeyword' => true,
            'analysisEnabled' => (bool)$post?->seoMeta,
            'readabilityEnabled' => true,
            'faviconUrl' => asset('favicon/favicon-32x32.webp'),
            'previewBaseUrl' => url('/'),
            'previewFallbackPath' => '/post-slug',

            'variables' => collect(SeoTemplateVariableEnum::cases())
                ->filter(fn(SeoTemplateVariableEnum $variable) => $variable->isAvailableFor('post'))
                ->map(fn(SeoTemplateVariableEnum $variable) => [
                    'token' => $variable->token(),
                    'label' => $variable->label(),
                    'description' => $variable->description(),
                ])
                ->values()
                ->all(),

            'templates' => [
                'title' => get_setting('seo', 'post_seo_title'),
                'description' => get_setting('seo', 'post_seo_description'),
                'ogTitle' => get_setting('seo', 'post_social_seo_title'),
                'ogDescription' => get_setting('seo', 'post_social_seo_description'),
            ],

            'values' => [
                'entityTitle' => old('title', $post->title ?? ''),
                'slug' => old('slug', $post->slug ?? ''),
                'siteTitle' => $siteTitle,
                'separator' => get_setting('seo', 'separator'),

                'variableValues' => [
                    SeoTemplateVariableEnum::SITE_TITLE->token() => $siteTitle,
                    SeoTemplateVariableEnum::SEPARATOR->token() => get_setting('seo', 'separator'),
                    SeoTemplateVariableEnum::POST_TITLE->token() => old('title', $post->title ?? ''),
                ],

                'focusKeyword' => old('seo.focus_keyword', $post->seoMeta?->focus_keyword ?? ''),

                'seoTitle' => old('seo.title', $post->seoMeta?->title ?? ''),
                'seoDescription' => old('seo.description', $post->seoMeta?->description ?? ''),

                'canonicalUrl' => old('seo.canonical_url', $post->seoMeta?->canonical_url ?? ''),
                'robots' => old('seo.robots', $post->seoMeta?->robots ?? ''),

                'ogTitle' => old('seo.og_title', $post->seoMeta?->og_title ?? ''),
                'ogDescription' => old('seo.og_description', $post->seoMeta?->og_description ?? ''),
                'ogImageId' => old('seo.og_image.id', $post->seoMeta?->og_image_id ?? null),
                'ogImagePath' => old('seo.og_image.path', $post->seoMeta?->ogImage?->url ?? ''),

                'xTitle' => old('seo.x_title', $post->seoMeta?->x_title ?? ''),
                'xDescription' => old('seo.x_description', $post->seoMeta?->x_description ?? ''),
                'xImageId' => old('seo.x_image.id', $post->seoMeta?->x_image_id ?? null),
                'xImagePath' => old('seo.x_image.path', $post->seoMeta?->xImage?->url ?? ''),
            ],

            'analysis' => $post
                ? $this->buildSeoAnalysisViewDataAction->execute($post)
                : null,
        ];
    }
}
