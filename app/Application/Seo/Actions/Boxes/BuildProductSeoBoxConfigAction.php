<?php

namespace App\Application\Seo\Actions\Boxes;

use App\Application\Seo\Actions\Analysis\BuildSeoAnalysisViewDataAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

final class BuildProductSeoBoxConfigAction
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
    public function execute(?Product $product): array
    {
        $siteTitle = $this->getSeoDefaultsAction->execute()['site_name'];

        return [
            'entityType' => 'product',
            'defaultTab' => 'general',
            'primaryTitleToken' => SeoTemplateVariableEnum::PRODUCT_TITLE->token(),
            'showFocusKeyword' => true,
            'analysisEnabled' => (bool)$product?->seoMeta,
            'readabilityEnabled' => true,
            'faviconUrl' => asset('favicon/favicon-32x32.webp'),
            'previewBaseUrl' => url('/'),
            'previewFallbackPath' => '/product-slug',

            'variables' => collect(SeoTemplateVariableEnum::cases())
                ->filter(fn(SeoTemplateVariableEnum $variable) => $variable->isAvailableFor('product'))
                ->map(fn(SeoTemplateVariableEnum $variable) => [
                    'token' => $variable->token(),
                    'label' => $variable->label(),
                    'description' => $variable->description(),
                ])
                ->values()
                ->all(),

            'templates' => [
                'title' => get_setting('seo', 'product_seo_title'),
                'description' => get_setting('seo', 'product_seo_description'),
                'ogTitle' => get_setting('seo', 'product_social_seo_title'),
                'ogDescription' => get_setting('seo', 'product_social_seo_description'),
            ],

            'values' => [
                'entityTitle' => old('title', $product->title ?? ''),
                'slug' => old('slug', $product->slug ?? ''),
                'siteTitle' => $siteTitle,
                'separator' => get_setting('seo', 'separator'),

                'variableValues' => [
                    SeoTemplateVariableEnum::SITE_TITLE->token() => $siteTitle,
                    SeoTemplateVariableEnum::SEPARATOR->token() => get_setting('seo', 'separator'),
                    SeoTemplateVariableEnum::PRODUCT_TITLE->token() => old('title', $product->title ?? ''),
                ],

                'focusKeyword' => old('seo.focus_keyword', $product->seoMeta?->focus_keyword ?? ''),

                'seoTitle' => old('seo.title', $product->seoMeta?->title ?? ''),
                'seoDescription' => old('seo.description', $product->seoMeta?->description ?? ''),

                'canonicalUrl' => old('seo.canonical_url', $product->seoMeta?->canonical_url ?? ''),
                'robots' => old('seo.robots', $product->seoMeta?->robots ?? ''),

                'ogTitle' => old('seo.og_title', $product->seoMeta?->og_title ?? ''),
                'ogDescription' => old('seo.og_description', $product->seoMeta?->og_description ?? ''),
                'ogImageId' => old('seo.og_image.id', $product->seoMeta?->og_image_id ?? null),
                'ogImagePath' => old('seo.og_image.path', $product->seoMeta?->ogImage?->url ?? ''),

                'xTitle' => old('seo.x_title', $product->seoMeta?->x_title ?? ''),
                'xDescription' => old('seo.x_description', $product->seoMeta?->x_description ?? ''),
                'xImageId' => old('seo.x_image.id', $product->seoMeta?->x_image_id ?? null),
                'xImagePath' => old('seo.x_image.path', $product->seoMeta?->xImage?->url ?? ''),
            ],

            'analysis' => $product
                ? $this->buildSeoAnalysisViewDataAction->execute($product)
                : null,
        ];
    }
}
