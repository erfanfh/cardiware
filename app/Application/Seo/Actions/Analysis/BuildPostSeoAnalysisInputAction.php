<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\Actions\ExtractFirstParagraphAction;
use App\Application\Seo\Actions\ExtractImageStatsAction;
use App\Application\Seo\Actions\ExtractLinkStatsAction;
use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\Services\HtmlContentAnalyzer;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;

final class BuildPostSeoAnalysisInputAction
{
    public function __construct(
        protected HtmlContentAnalyzer         $htmlContentAnalyzer,
        protected PersianTextNormalizer       $persianTextNormalizer,
        protected PostSeoVariableDataBuilder  $postSeoVariableDataBuilder,
        protected SeoTemplateParser           $seoTemplateParser,
        protected ExtractFirstParagraphAction $extractFirstParagraphAction,
        protected ExtractLinkStatsAction      $extractLinkStatsAction,
        protected ExtractImageStatsAction     $extractImageStatsAction,
    )
    {
    }

    public function execute(Post $post): SeoAnalysisInputDTO
    {
        $post->loadMissing('seoMeta');

        $seoMeta = $post->seoMeta;
        $data = $this->postSeoVariableDataBuilder->build($post);

        $titleTemplate = $seoMeta?->title ?: get_setting('seo', 'post_seo_title') ?: $post->title;
        $descriptionTemplate = $seoMeta?->description ?: get_setting('seo', 'post_seo_description') ?: '';

        $resolvedSeoTitle = $this->seoTemplateParser->parse((string)$titleTemplate, $data);
        $resolvedSeoDescription = $this->seoTemplateParser->parse((string)$descriptionTemplate, $data);

        $htmlContent = (string)($post->body ?? '');
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
            postId: $post->id ? (int)$post->id : null,
            title: (string)($post->title ?? ''),
            slug: (string)($post->slug ?? ''),
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
}
