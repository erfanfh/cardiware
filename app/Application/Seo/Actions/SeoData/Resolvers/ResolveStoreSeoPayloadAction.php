<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Domain\Marketplace\Models\Store;

final class ResolveStoreSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction      $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction $resolveCanonicalUrlAction,
        protected SeoTemplateParser         $seoTemplateParser,
    )
    {
    }

    public function execute(Store $store): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $variables = [
            'store_name' => $store->title,
            'site_name' => $defaults['site_name'],
            'separator' => get_setting('seo', 'separator', '|'),
        ];

        $titleTemplate = $store->seoMeta?->title
            ?: get_setting('seo', 'store_seo_title')
                ?: $store->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $descriptionTemplate = $store->seoMeta?->description
            ?: get_setting('seo', 'store_seo_description')
                ?: ($store->description ?? '');

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $store->seoMeta?->og_title
            ?: get_setting('seo', 'store_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $store->seoMeta?->og_description
            ?: get_setting('seo', 'store_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $xTitleTemplate = $store->seoMeta?->x_title;
        $xTitle = filled($xTitleTemplate)
            ? $this->seoTemplateParser->parse($xTitleTemplate, $variables)
            : null;

        $xDescriptionTemplate = $store->seoMeta?->x_description;
        $xDescription = filled($xDescriptionTemplate)
            ? $this->seoTemplateParser->parse($xDescriptionTemplate, $variables)
            : null;

        $image = $store->seoMeta?->ogImage ?: $store->picture;

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $store->seoMeta?->canonical_url,
            fallbackUrl: route('stores.detail', $store),
        );

        $publishedTime = $store->created_at?->utc()->toIso8601String();
        $modifiedTime = $this->resolveModifiedTime($store, $publishedTime);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $store->seoMeta?->robots ?: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $store->title,

            ogTitle: $ogTitle,
            ogDescription: $ogDescription,

            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],

            xTitle: $xTitle,
            xDescription: $xDescription,
            xImageUrl: $store->seoMeta?->xImage?->url ?? $store->picture?->url,

            pageImageUrl: $store->picture?->url,
            pageImageWidth: $store->picture?->primaryVariant?->width,
            pageImageHeight: $store->picture?->primaryVariant?->height,
            pageImageCaption: $store->title,

            publishedTime: $publishedTime,
            modifiedTime: $modifiedTime,

            keywords: $this->resolveKeywords($store),

            projectStatus: null,
        );
    }

    /**
     * Store usually doesn't have strict SEO keywords relation
     */
    protected function resolveKeywords(Store $store): array
    {
        if (method_exists($store, 'products') && $store->relationLoaded('products')) {
            return $store->products
                ->pluck('title')
                ->filter(static fn($value) => filled($value))
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * Same logic as Project but simplified
     */
    protected function resolveModifiedTime(Store $store, ?string $publishedTime): ?string
    {
        if (!$store->updated_at) {
            return $publishedTime;
        }

        if ($store->created_at && $store->updated_at <= $store->created_at) {
            return $publishedTime;
        }

        return $store->updated_at->utc()->toIso8601String();
    }
}
