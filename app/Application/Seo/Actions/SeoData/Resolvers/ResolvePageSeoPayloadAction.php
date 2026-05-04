<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PageSeoVariableDataBuilder;
use App\Domain\Content\Models\Page;
use App\Infrastructure\Support\ReadingTimeEstimator;

final class ResolvePageSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction       $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction  $resolveCanonicalUrlAction,
        protected SeoTemplateParser          $seoTemplateParser,
        protected PageSeoVariableDataBuilder $pageSeoVariableDataBuilder,
        protected ReadingTimeEstimator       $readingTimeEstimator,
    )
    {
    }

    public function execute(Page $page): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->pageSeoVariableDataBuilder->build($page);

        $titleTemplate = $page->seoMeta?->title
            ?: get_setting('seo', 'page_seo_title')
                ?: $page->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $descriptionTemplate = $page->seoMeta?->description
            ?: get_setting('seo', 'page_seo_description')
                ?: $page->excerpt();

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $page->seoMeta?->og_title
            ?: get_setting('seo', 'page_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $page->seoMeta?->og_description
            ?: get_setting('seo', 'page_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $xTitleTemplate = $page->seoMeta?->x_title;
        $xTitle = filled($xTitleTemplate)
            ? $this->seoTemplateParser->parse($xTitleTemplate, $variables)
            : null;

        $xDescriptionTemplate = $page->seoMeta?->x_description;
        $xDescription = filled($xDescriptionTemplate)
            ? $this->seoTemplateParser->parse($xDescriptionTemplate, $variables)
            : null;

        $ogImage = $page->seoMeta?->ogImage ?: $page->picture;

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $page->seoMeta?->canonical_url,
            fallbackUrl: $page->url,
        );

        $publishedTime = $page->published_at?->utc()->toIso8601String();
        $modifiedTime = $this->resolveModifiedTime($page, $publishedTime);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $page->seoMeta?->robots ?: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $page->title,

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
            xImageUrl: $page->seoMeta?->xImage?->url,

            pageImageUrl: $page->picture?->url,
            pageImageWidth: $page->picture?->primaryVariant?->width,
            pageImageHeight: $page->picture?->primaryVariant?->height,
            pageImageCaption: $page->title,

            publishedTime: $publishedTime,
            modifiedTime: $modifiedTime,

            readingTimeLabel: $this->readingTimeEstimator->estimate($page->body) . ' دقیقه',
        );
    }

    protected function resolveModifiedTime(Page $page, ?string $publishedTime): ?string
    {
        if (!$page->updated_at) {
            return $publishedTime;
        }

        if ($page->published_at && $page->updated_at <= $page->published_at) {
            return $publishedTime;
        }

        return $page->updated_at->utc()->toIso8601String();
    }
}
