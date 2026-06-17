<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\Actions\ExtractFirstParagraphAction;
use App\Application\Seo\Actions\ExtractImageStatsAction;
use App\Application\Seo\Actions\ExtractLinkStatsAction;
use App\Application\Seo\Contracts\SeoAnalysisInputBuilderInterface;
use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\Services\HtmlContentAnalyzer;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\StoreSeoVariableDataBuilder;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class BuildStoreSeoAnalysisInputAction implements SeoAnalysisInputBuilderInterface
{
    public function __construct(
        protected HtmlContentAnalyzer         $htmlContentAnalyzer,
        protected PersianTextNormalizer       $persianTextNormalizer,
        protected StoreSeoVariableDataBuilder $storeSeoVariableDataBuilder,
        protected SeoTemplateParser           $seoTemplateParser,
        protected ExtractFirstParagraphAction $extractFirstParagraphAction,
        protected ExtractLinkStatsAction      $extractLinkStatsAction,
        protected ExtractImageStatsAction     $extractImageStatsAction,
    )
    {
    }

    public function execute(Model $model): SeoAnalysisInputDTO
    {
        if (!$model instanceof Store) {
            throw new InvalidArgumentException('Model must be instance of Store.');
        }

        $store = $model;
        $store->loadMissing('seoMeta');

        $seoMeta = $store->seoMeta;
        $data = $this->storeSeoVariableDataBuilder->build($store);

        $titleTemplate = $seoMeta?->title
            ?: get_setting('seo', 'store_seo_title')
                ?: $store->title;

        $descriptionTemplate = $seoMeta?->description
            ?: get_setting('seo', 'store_seo_description')
                ?: '';

        $resolvedSeoTitle = $this->seoTemplateParser->parse((string)$titleTemplate, $data);
        $resolvedSeoDescription = $this->seoTemplateParser->parse((string)$descriptionTemplate, $data);

        $htmlContent = $this->resolveMainContent($store);
        $contentAnalysis = $this->htmlContentAnalyzer->analyze($htmlContent);
        $plainTextContent = (string)($contentAnalysis['plain_text'] ?? '');

        $wordCount = $this->persianTextNormalizer->wordCount($plainTextContent);
        $firstParagraph = (string)($this->extractFirstParagraphAction->execute($htmlContent) ?? '');

        $linkStats = $this->extractLinkStatsAction->execute(
            html: $htmlContent,
            siteUrl: url('/'),
        );

        $imageStats = $this->extractImageStatsAction->execute($htmlContent);

        return new SeoAnalysisInputDTO(
            seoableType: Store::class,
            seoableId: $store->id ? (int)$store->id : null,
            title: (string)($store->title ?? ''),
            slug: (string)($store->slug ?? ''),
            htmlContent: $htmlContent,
            plainTextContent: $plainTextContent,
            wordCount: $wordCount,
            focusKeyword: $seoMeta?->focus_keyword,
            resolvedSeoTitle: (string)$resolvedSeoTitle,
            resolvedSeoDescription: (string)$resolvedSeoDescription,
            h1Count: (int)($contentAnalysis['h1_count'] ?? 0),
            h1Texts: array_values((array)($contentAnalysis['h1_texts'] ?? [])),
            h2Texts: array_values((array)($contentAnalysis['h2_texts'] ?? [])),
            h3Texts: array_values((array)($contentAnalysis['h3_texts'] ?? [])),
            firstParagraphText: $firstParagraph,
            internalLinkCount: (int)($linkStats['internal_link_count'] ?? 0),
            outboundLinkCount: (int)($linkStats['outbound_link_count'] ?? 0),
            outboundFollowLinkCount: (int)($linkStats['outbound_follow_link_count'] ?? 0),
            imageCount: (int)($imageStats['image_count'] ?? 0),
            imageAltTexts: array_values((array)($imageStats['image_alt_texts'] ?? [])),
            internalLinkAnchorTexts: array_values((array)($linkStats['internal_link_anchor_texts'] ?? [])),
        );
    }

    protected function resolveMainContent(Store $store): string
    {
        if (isset($store->body) && is_string($store->body)) {
            return $store->body;
        }

        return '';
    }
}
