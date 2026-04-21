<?php

namespace App\Application\Seo\Actions\Analysis;

use App\Application\Seo\DTOs\SeoAnalysisInputDTO;
use App\Application\Seo\Services\HtmlContentAnalyzer;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;

final class BuildPostSeoAnalysisInputAction
{
    public function __construct(
        protected HtmlContentAnalyzer        $htmlContentAnalyzer,
        protected PersianTextNormalizer      $persianTextNormalizer,
        protected PostSeoVariableDataBuilder $postSeoVariableDataBuilder,
        protected SeoTemplateParser          $seoTemplateParser,
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

        $resolvedSeoTitle = $this->seoTemplateParser->parse($titleTemplate, $data);
        $resolvedSeoDescription = $this->seoTemplateParser->parse($descriptionTemplate, $data);

        $contentAnalysis = $this->htmlContentAnalyzer->analyze((string)$post->body);

        return new SeoAnalysisInputDTO(
            title: (string)($post->title ?? ''),
            slug: (string)($post->slug ?? ''),
            htmlContent: (string)($post->body ?? ''),
            plainTextContent: $contentAnalysis['plain_text'],
            wordCount: $this->persianTextNormalizer->wordCount($contentAnalysis['plain_text']),
            focusKeyword: $seoMeta?->focus_keyword,
            resolvedSeoTitle: $resolvedSeoTitle,
            resolvedSeoDescription: $resolvedSeoDescription,
            h1Count: $contentAnalysis['h1_count'],
            h1Texts: $contentAnalysis['h1_texts'],
            h2Texts: $contentAnalysis['h2_texts'],
            h3Texts: $contentAnalysis['h3_texts'],
        );
    }
}
