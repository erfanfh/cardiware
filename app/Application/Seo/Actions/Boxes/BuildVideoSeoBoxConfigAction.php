<?php

namespace App\Application\Seo\Actions\Boxes;

use App\Application\Seo\Actions\Analysis\BuildSeoAnalysisViewDataAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;
use App\Domain\Video\Models\Video;

final class BuildVideoSeoBoxConfigAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaults,
        private readonly BuildSeoAnalysisViewDataAction $analysis,
    ) {}

    public function execute(?Video $video): array
    {
        $siteTitle = $this->defaults->execute()['site_name'];
        $separator = get_setting('seo', 'separator') ?: '|';
        return [
            'entityType' => 'video',
            'defaultTab' => 'general',
            'primaryTitleToken' => SeoTemplateVariableEnum::VIDEO_TITLE->token(),
            'showFocusKeyword' => true,
            'analysisEnabled' => (bool)$video?->seoMeta,
            'readabilityEnabled' => true,
            'faviconUrl' => asset('favicon/favicon-32x32.webp'),
            'previewBaseUrl' => route('videos.index'),
            'previewFallbackPath' => '/video-slug',
            'variables' => collect(SeoTemplateVariableEnum::cases())
                ->filter(fn ($v) => $v->isAvailableFor('video'))
                ->map(fn ($v) => ['token' => $v->token(), 'label' => $v->label(), 'description' => $v->description()])
                ->values()->all(),
            'templates' => [
                'title' => '{video_title} {separator} {site_title}',
                'description' => '',
                'ogTitle' => '{video_title}',
                'ogDescription' => '',
            ],
            'values' => [
                'entityTitle' => old('title', $video?->title ?? ''),
                'slug' => old('slug', $video?->slug ?? ''),
                'siteTitle' => $siteTitle,
                'separator' => $separator,
                'variableValues' => [
                    SeoTemplateVariableEnum::SITE_TITLE->token() => $siteTitle,
                    SeoTemplateVariableEnum::SEPARATOR->token() => $separator,
                    SeoTemplateVariableEnum::VIDEO_TITLE->token() => old('title', $video?->title ?? ''),
                ],
                'focusKeyword' => old('seo.focus_keyword', $video?->seoMeta?->focus_keyword ?? ''),
                'seoTitle' => old('seo.title', $video?->seoMeta?->title ?? ''),
                'seoDescription' => old('seo.description', $video?->seoMeta?->description ?? ''),
                'canonicalUrl' => old('seo.canonical_url', $video?->seoMeta?->canonical_url ?? ''),
                'robots' => old('seo.robots', $video?->seoMeta?->robots ?? ''),
                'ogTitle' => old('seo.og_title', $video?->seoMeta?->og_title ?? ''),
                'ogDescription' => old('seo.og_description', $video?->seoMeta?->og_description ?? ''),
                'ogImageId' => old('seo.og_image.id', $video?->seoMeta?->og_image_id),
                'ogImagePath' => old('seo.og_image.path', $video?->seoMeta?->ogImage?->url ?? ''),
                'xTitle' => old('seo.x_title', $video?->seoMeta?->x_title ?? ''),
                'xDescription' => old('seo.x_description', $video?->seoMeta?->x_description ?? ''),
                'xImageId' => old('seo.x_image.id', $video?->seoMeta?->x_image_id),
                'xImagePath' => old('seo.x_image.path', $video?->seoMeta?->xImage?->url ?? ''),
            ],
            'analysis' => $video && $video->seoAnalysis ? $this->analysis->execute($video) : null,
        ];
    }
}
