<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\ProductSeoVariableDataBuilder;
use App\Domain\Catalog\Models\Product;
use App\Infrastructure\Support\ReadingTimeEstimator;

final class ResolveProductSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction          $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction     $resolveCanonicalUrlAction,
        protected SeoTemplateParser             $seoTemplateParser,
        protected ProductSeoVariableDataBuilder $productSeoVariableDataBuilder,
        protected ReadingTimeEstimator          $readingTimeEstimator,
        protected PersianTextNormalizer         $textNormalizer,
    )
    {
    }

    public function execute(Product $product): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->productSeoVariableDataBuilder->build($product);

        $titleTemplate = $product->seoMeta?->title
            ?: get_setting('seo', 'product_seo_title')
                ?: $product->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $descriptionTemplate = $product->seoMeta?->description
            ?: get_setting('seo', 'product_seo_description')
                ?: $this->resolveExcerpt($product);

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $product->seoMeta?->og_title
            ?: get_setting('seo', 'product_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $product->seoMeta?->og_description
            ?: get_setting('seo', 'product_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $xTitleTemplate = $product->seoMeta?->x_title;
        $xTitle = filled($xTitleTemplate)
            ? $this->seoTemplateParser->parse($xTitleTemplate, $variables)
            : null;

        $xDescriptionTemplate = $product->seoMeta?->x_description;
        $xDescription = filled($xDescriptionTemplate)
            ? $this->seoTemplateParser->parse($xDescriptionTemplate, $variables)
            : null;

        $ogImage = $product->seoMeta?->ogImage ?: $product->picture;

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $product->seoMeta?->canonical_url,
            fallbackUrl: $product->url,
        );

        $publishedTime = $product->published_at?->utc()->toIso8601String();
        $modifiedTime = $this->resolveModifiedTime($product, $publishedTime);
        
        $articleContentForAnalysis = $this->resolveArticleContentForAnalysis($product);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $product->seoMeta?->robots ?: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $product->title,

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
            xImageUrl: $product->seoMeta?->xImage?->url,

            pageImageUrl: $product->picture?->url,
            pageImageWidth: $product->picture?->primaryVariant?->width,
            pageImageHeight: $product->picture?->primaryVariant?->height,
            pageImageCaption: $product->title,

            publishedTime: $publishedTime,
            modifiedTime: $modifiedTime,

            wordCount: $this->estimateWordCount($articleContentForAnalysis),
            commentCount: $product->approved_comments_count ?? 0,
            keywords: $this->resolveKeywords($product),
            articleSections: $this->resolveArticleSections($product),

            readingTimeLabel: $this->readingTimeEstimator->estimate($articleContentForAnalysis) . ' دقیقه',
        );
    }

    protected function resolveModifiedTime(Product $product, ?string $publishedTime): ?string
    {
        if (!$product->updated_at) {
            return $publishedTime;
        }

        if ($product->published_at && $product->updated_at <= $product->published_at) {
            return $publishedTime;
        }

        return $product->updated_at->utc()->toIso8601String();
    }

    protected function resolveKeywords(Product $product): array
    {
        return $product->tags
            ->pluck('title')
            ->filter(static fn($value) => filled($value))
            ->values()
            ->all();
    }

    protected function resolveArticleSections(Product $product): array
    {
        return $product->categories
            ->pluck('title')
            ->filter(static fn($value) => filled($value))
            ->values()
            ->all();
    }

    protected function estimateWordCount(string $content): int
    {
        return $this->textNormalizer->wordCount($content);
    }

    protected function resolveExcerpt(Product $product): string
    {
        if (method_exists($product, 'excerpt')) {
            return (string)$product->excerpt();
        }

        return (string)($product->excerpt ?? '');
    }

    protected function resolveMainContent(Product $product): string
    {
        if (isset($product->body) && is_string($product->body)) {
            return $product->body;
        }

        return (string)($product->content ?? '');
    }

    protected function resolveArticleContentForAnalysis(Product $product): string
    {
        $content = $this->resolveMainContent($product);

        if (blank($content)) {
            return '';
        }

        $content = preg_replace(
            '/<div\b[^>]*class="[^"]*\bwp-migrated-video\b[^"]*"[^>]*>.*?<\/div>/is',
            ' ',
            $content
        ) ?? $content;

        $content = preg_replace(
            '/<div\b[^>]*class="[^"]*\bdownload-box\b[^"]*"[^>]*>.*?<\/div>/is',
            ' ',
            $content
        ) ?? $content;

        return $content;
    }
}
