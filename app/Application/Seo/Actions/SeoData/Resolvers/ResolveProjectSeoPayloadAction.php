<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\ProjectSeoVariableDataBuilder;
use App\Domain\Project\Models\Project;

final class ResolveProjectSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction          $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction     $resolveCanonicalUrlAction,
        protected SeoTemplateParser             $seoTemplateParser,
        protected ProjectSeoVariableDataBuilder $projectSeoVariableDataBuilder,
    )
    {
    }

    public function execute(Project $project): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->projectSeoVariableDataBuilder->build($project);

        $titleTemplate = $project->seoMeta?->title
            ?: get_setting('seo', 'project_seo_title')
                ?: $project->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $descriptionTemplate = $project->seoMeta?->description
            ?: get_setting('seo', 'project_seo_description')
                ?: $project->excerpt();

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $project->seoMeta?->og_title
            ?: get_setting('seo', 'project_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $project->seoMeta?->og_description
            ?: get_setting('seo', 'project_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $xTitleTemplate = $project->seoMeta?->x_title;
        $xTitle = filled($xTitleTemplate)
            ? $this->seoTemplateParser->parse($xTitleTemplate, $variables)
            : null;

        $xDescriptionTemplate = $project->seoMeta?->x_description;
        $xDescription = filled($xDescriptionTemplate)
            ? $this->seoTemplateParser->parse($xDescriptionTemplate, $variables)
            : null;

        $ogImage = $project->seoMeta?->ogImage ?: $project->cover;

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $project->seoMeta?->canonical_url,
            fallbackUrl: front_route('projects.detail', $project),
        );

        $publishedTime = $project->created_at?->utc()->toIso8601String();
        $modifiedTime = $this->resolveModifiedTime($project, $publishedTime);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $project->seoMeta?->robots ?: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $project->title,

            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: [
                'url' => $ogImage?->url,
                'width' => $ogImage?->primaryVariant?->width,
                'height' => $ogImage?->primaryVariant?->height,
                'type' => $ogImage?->primaryVariant?->mime_type,
            ],

            xTitle: $xTitle,
            xDescription: $xDescription,
            xImageUrl: $project->seoMeta?->xImage?->url,

            pageImageUrl: $project->cover?->url,
            pageImageWidth: $project->cover?->primaryVariant?->width,

            pageImageHeight: $project->cover?->primaryVariant?->height,
            pageImageCaption: $project->title,
            publishedTime: $publishedTime,
            modifiedTime: $modifiedTime,

            keywords: $this->resolveKeywords($project),
            projectStatus: $this->resolveProjectStatus($project),
        );
    }

    protected function resolveModifiedTime(Project $project, ?string $publishedTime): ?string
    {
        if (!$project->updated_at) {
            return $publishedTime;
        }

        if ($project->created_at && $project->updated_at <= $project->created_at) {
            return $publishedTime;
        }

        return $project->updated_at->utc()->toIso8601String();
    }

    /**
     * @return array<int, string>
     */
    protected function resolveKeywords(Project $project): array
    {
        if (method_exists($project, 'tags') && $project->relationLoaded('tags')) {
            return $project->tags
                ->pluck('title')
                ->filter(static fn($value) => filled($value))
                ->values()
                ->all();
        }

        return [];
    }

    protected function resolveProjectStatus(Project $project): ?string
    {
        return $project->status_label
            ?? $project->status?->value
            ?? $project->status
            ?? null;
    }
}
