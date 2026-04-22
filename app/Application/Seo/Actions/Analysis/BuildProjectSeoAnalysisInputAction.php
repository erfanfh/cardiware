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
use App\Application\Seo\Support\VariableBuilders\ProjectSeoVariableDataBuilder;
use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class BuildProjectSeoAnalysisInputAction implements SeoAnalysisInputBuilderInterface
{
    public function __construct(
        protected HtmlContentAnalyzer           $htmlContentAnalyzer,
        protected PersianTextNormalizer         $persianTextNormalizer,
        protected ProjectSeoVariableDataBuilder $projectSeoVariableDataBuilder,
        protected SeoTemplateParser             $seoTemplateParser,
        protected ExtractFirstParagraphAction   $extractFirstParagraphAction,
        protected ExtractLinkStatsAction        $extractLinkStatsAction,
        protected ExtractImageStatsAction       $extractImageStatsAction,
    )
    {
    }

    public function execute(Model $model): SeoAnalysisInputDTO
    {
        if (!$model instanceof Project) {
            throw new InvalidArgumentException('Model must be instance of Project.');
        }

        $project = $model;
        $project->loadMissing('seoMeta');

        $seoMeta = $project->seoMeta;
        $data = $this->projectSeoVariableDataBuilder->build($project);

        $titleTemplate = $seoMeta?->title
            ?: get_setting('seo', 'project_seo_title')
                ?: $project->title;

        $descriptionTemplate = $seoMeta?->description
            ?: get_setting('seo', 'project_seo_description')
                ?: '';

        $resolvedSeoTitle = $this->seoTemplateParser->parse((string)$titleTemplate, $data);
        $resolvedSeoDescription = $this->seoTemplateParser->parse((string)$descriptionTemplate, $data);

        $htmlContent = $this->resolveMainContent($project);
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
            seoableType: Project::class,
            seoableId: $project->id ? (int)$project->id : null,
            title: (string)($project->title ?? ''),
            slug: (string)($project->slug ?? ''),
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

    protected function resolveMainContent(Project $project): string
    {
        if (isset($project->body) && is_string($project->body)) {
            return $project->body;
        }

        return (string)($project->content ?? '');
    }
}
