<?php

namespace App\Application\Seo\Actions\Boxes;

use App\Application\Seo\Actions\Analysis\BuildSeoAnalysisViewDataAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

final class BuildStoreSeoBoxConfigAction
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
    public function execute(?Store $store): array
    {
        $siteTitle = $this->getSeoDefaultsAction->execute()['site_name'];

        return [
            'entityType' => 'store',
            'defaultTab' => 'general',
            'primaryTitleToken' => SeoTemplateVariableEnum::STORE_TITLE->token(),
            'showFocusKeyword' => true,
            'analysisEnabled' => (bool)$store?->seoMeta,
            'readabilityEnabled' => true,
            'faviconUrl' => asset('favicon/favicon-32x32.webp'),
            'previewBaseUrl' => url('/stores'),
            'previewFallbackPath' => '/sample-store',

            'variables' => collect(SeoTemplateVariableEnum::cases())
                ->filter(fn(SeoTemplateVariableEnum $variable) => $variable->isAvailableFor('store'))
                ->map(fn(SeoTemplateVariableEnum $variable) => [
                    'token' => $variable->token(),
                    'label' => $variable->label(),
                    'description' => $variable->description(),
                ])
                ->values()
                ->all(),

            'templates' => [
                'title' => get_setting('seo', 'store_seo_title'),
                'description' => get_setting('seo', 'store_seo_description'),
                'ogTitle' => get_setting('seo', 'store_social_seo_title'),
                'ogDescription' => get_setting('seo', 'store_social_seo_description'),
            ],

            'values' => [
                'entityTitle' => old('title', $store->title ?? ''),
                'slug' => old('slug', $store->slug ?? ''),
                'siteTitle' => $siteTitle,
                'separator' => get_setting('seo', 'separator'),

                'variableValues' => [
                    SeoTemplateVariableEnum::SITE_TITLE->token() => $siteTitle,
                    SeoTemplateVariableEnum::SEPARATOR->token() => get_setting('seo', 'separator'),
                    SeoTemplateVariableEnum::STORE_TITLE->token() => old('title', $store->title ?? ''),
                ],

                'focusKeyword' => old('seo.focus_keyword', $store->seoMeta?->focus_keyword ?? ''),

                'seoTitle' => old('seo.title', $store->seoMeta?->title ?? ''),
                'seoDescription' => old('seo.description', $store->seoMeta?->description ?? ''),

                'canonicalUrl' => old('seo.canonical_url', $store->seoMeta?->canonical_url ?? ''),
                'robots' => old('seo.robots', $store->seoMeta?->robots ?? ''),

                'ogTitle' => old('seo.og_title', $store->seoMeta?->og_title ?? ''),
                'ogDescription' => old('seo.og_description', $store->seoMeta?->og_description ?? ''),
                'ogImageId' => old('seo.og_image.id', $store->seoMeta?->og_image_id ?? null),
                'ogImagePath' => old('seo.og_image.path', $store->seoMeta?->ogImage?->url ?? ''),

                'xTitle' => old('seo.x_title', $store->seoMeta?->x_title ?? ''),
                'xDescription' => old('seo.x_description', $store->seoMeta?->x_description ?? ''),
                'xImageId' => old('seo.x_image.id', $store->seoMeta?->x_image_id ?? null),
                'xImagePath' => old('seo.x_image.path', $store->seoMeta?->xImage?->url ?? ''),
            ],

            'analysis' => $store
                ? $this->buildSeoAnalysisViewDataAction->execute($store)
                : null,
        ];
    }
}
