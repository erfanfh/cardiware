<?php

namespace App\Application\Seo\Actions\Boxes;

use App\Application\Seo\Actions\Analysis\BuildSeoAnalysisViewDataAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Project\Models\Project;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

final class BuildProjectSeoBoxConfigAction
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
    public function execute(?Project $project): array
    {
        $siteTitle = $this->getSeoDefaultsAction->execute()['site_name'];

        return [
            'entityType' => 'project',
            'defaultTab' => 'general',
            'primaryTitleToken' => SeoTemplateVariableEnum::PROJECT_TITLE->token(),
            'showFocusKeyword' => true,
            'analysisEnabled' => (bool)$project?->seoMeta,
            'readabilityEnabled' => true,
            'faviconUrl' => asset('favicon/favicon-32x32.webp'),
            'previewBaseUrl' => url('/projects'),
            'previewFallbackPath' => '/sample-project',

            'variables' => collect(SeoTemplateVariableEnum::cases())
                ->filter(fn(SeoTemplateVariableEnum $variable) => $variable->isAvailableFor('project'))
                ->map(fn(SeoTemplateVariableEnum $variable) => [
                    'token' => $variable->token(),
                    'label' => $variable->label(),
                    'description' => $variable->description(),
                ])
                ->values()
                ->all(),

            'templates' => [
                'title' => get_setting('seo', 'project_seo_title'),
                'description' => get_setting('seo', 'project_seo_description'),
                'ogTitle' => get_setting('seo', 'project_social_seo_title'),
                'ogDescription' => get_setting('seo', 'project_social_seo_description'),
            ],

            'values' => [
                'entityTitle' => old('title', $project->title ?? ''),
                'slug' => old('slug', $project->slug ?? ''),
                'siteTitle' => $siteTitle,
                'separator' => get_setting('seo', 'separator'),

                'variableValues' => [
                    SeoTemplateVariableEnum::SITE_TITLE->token() => $siteTitle,
                    SeoTemplateVariableEnum::SEPARATOR->token() => get_setting('seo', 'separator'),
                    SeoTemplateVariableEnum::PROJECT_TITLE->token() => old('title', $project->title ?? ''),
                ],

                'focusKeyword' => old('seo.focus_keyword', $project->seoMeta?->focus_keyword ?? ''),

                'seoTitle' => old('seo.title', $project->seoMeta?->title ?? ''),
                'seoDescription' => old('seo.description', $project->seoMeta?->description ?? ''),

                'canonicalUrl' => old('seo.canonical_url', $project->seoMeta?->canonical_url ?? ''),
                'robots' => old('seo.robots', $project->seoMeta?->robots ?? ''),

                'ogTitle' => old('seo.og_title', $project->seoMeta?->og_title ?? ''),
                'ogDescription' => old('seo.og_description', $project->seoMeta?->og_description ?? ''),
                'ogImageId' => old('seo.og_image.id', $project->seoMeta?->og_image_id ?? null),
                'ogImagePath' => old('seo.og_image.path', $project->seoMeta?->ogImage?->url ?? ''),

                'xTitle' => old('seo.x_title', $project->seoMeta?->x_title ?? ''),
                'xDescription' => old('seo.x_description', $project->seoMeta?->x_description ?? ''),
                'xImageId' => old('seo.x_image.id', $project->seoMeta?->x_image_id ?? null),
                'xImagePath' => old('seo.x_image.path', $project->seoMeta?->xImage?->url ?? ''),
            ],

            'analysis' => $project
                ? $this->buildSeoAnalysisViewDataAction->execute($project)
                : null,
        ];
    }
}
