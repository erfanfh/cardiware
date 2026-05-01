<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\ProjectListSeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;

final class ResolveProjectListSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction              $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction         $resolveCanonicalUrlAction,
        protected SeoTemplateParser                 $seoTemplateParser,
        protected ProjectListSeoVariableDataBuilder $projectListSeoVariableDataBuilder,
    )
    {
    }

    public function execute(): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->projectListSeoVariableDataBuilder->build();

        $titleTemplate = get_setting('seo', 'project_list_seo_title')
            ?: 'پروژه ها {separator} {site_title}';

        $descriptionTemplate = get_setting('seo', 'project_list_seo_description')
            ?: 'جدیدترین پروژه‌ها، فایل‌ها، مودها و محتوای منتشر شده در {site_title} را ببینید.';

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $image = $this->resolvePrimaryImage();

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $this->resolveCurrentPageUrl(),
        );

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: 'پروژه‌ها',

            ogTitle: $title,
            ogDescription: $description,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],

            xTitle: $title,
            xDescription: $description,
            xImageUrl: $image?->url,

            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: 'پروژه‌ها',
        );
    }

    protected function resolveCurrentPageUrl(): string
    {
        $baseUrl = front_route('projects.list');
        $page = max(1, (int)request()->integer('page', 1));

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . '?page=' . $page;
    }

    protected function resolvePrimaryImage(): ?MediaFile
    {
        return null;
    }
}
