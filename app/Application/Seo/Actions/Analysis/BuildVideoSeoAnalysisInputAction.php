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
use App\Application\Seo\Support\VariableBuilders\VideoSeoVariableDataBuilder;
use App\Domain\Video\Models\Video;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class BuildVideoSeoAnalysisInputAction implements SeoAnalysisInputBuilderInterface
{
    public function __construct(
        private readonly HtmlContentAnalyzer $htmlAnalyzer,
        private readonly PersianTextNormalizer $normalizer,
        private readonly VideoSeoVariableDataBuilder $variables,
        private readonly SeoTemplateParser $parser,
        private readonly ExtractFirstParagraphAction $firstParagraph,
        private readonly ExtractLinkStatsAction $links,
        private readonly ExtractImageStatsAction $images,
    ) {}

    public function execute(Model $model): SeoAnalysisInputDTO
    {
        if (!$model instanceof Video) { throw new InvalidArgumentException('Model must be instance of Video.'); }
        $model->loadMissing('seoMeta');
        $meta = $model->seoMeta;
        $variables = $this->variables->build($model);
        $title = $this->parser->parse((string)($meta?->title ?: '{video_title} {separator} {site_title}'), $variables);
        $description = $this->parser->parse((string)($meta?->description ?: strip_tags((string)$model->excerpt)), $variables);
        $html = (string)($model->body ?: $model->excerpt);
        $content = $this->htmlAnalyzer->analyze($html);
        $plain = (string)($content['plain_text'] ?? '');
        $linkStats = $this->links->execute($html, url('/'));
        $imageStats = $this->images->execute($html);

        return new SeoAnalysisInputDTO(
            seoableType: Video::class, seoableId: $model->id ? (int)$model->id : null,
            title: (string)$model->title, slug: (string)$model->slug,
            htmlContent: $html, plainTextContent: $plain, wordCount: $this->normalizer->wordCount($plain),
            focusKeyword: $meta?->focus_keyword, resolvedSeoTitle: $title, resolvedSeoDescription: $description,
            h1Count: (int)($content['h1_count'] ?? 0), h1Texts: array_values((array)($content['h1_texts'] ?? [])),
            h2Texts: array_values((array)($content['h2_texts'] ?? [])), h3Texts: array_values((array)($content['h3_texts'] ?? [])),
            firstParagraphText: (string)($this->firstParagraph->execute($html) ?? ''),
            internalLinkCount: (int)($linkStats['internal_link_count'] ?? 0), outboundLinkCount: (int)($linkStats['outbound_link_count'] ?? 0),
            outboundFollowLinkCount: (int)($linkStats['outbound_follow_link_count'] ?? 0), imageCount: (int)($imageStats['image_count'] ?? 0),
            imageAltTexts: array_values((array)($imageStats['image_alt_texts'] ?? [])), internalLinkAnchorTexts: array_values((array)($linkStats['internal_link_anchor_texts'] ?? [])),
        );
    }
}
