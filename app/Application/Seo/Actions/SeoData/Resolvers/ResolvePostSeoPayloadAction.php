<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Services\PersianTextNormalizer;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Support\ReadingTimeEstimator;

final class ResolvePostSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction       $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction  $resolveCanonicalUrlAction,
        protected SeoTemplateParser          $seoTemplateParser,
        protected PostSeoVariableDataBuilder $postSeoVariableDataBuilder,
        protected ReadingTimeEstimator       $readingTimeEstimator,
        protected PersianTextNormalizer      $textNormalizer,
    )
    {
    }

    public function execute(Post $post): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->postSeoVariableDataBuilder->build($post);

        $titleTemplate = $post->seoMeta?->title
            ?: get_setting('seo', 'post_seo_title')
                ?: $post->title;

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $descriptionTemplate = $post->seoMeta?->description
            ?: get_setting('seo', 'post_seo_description')
                ?: $this->resolveExcerpt($post);

        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitleTemplate = $post->seoMeta?->og_title
            ?: get_setting('seo', 'post_social_seo_title')
                ?: $title;

        $ogTitle = $this->seoTemplateParser->parse($ogTitleTemplate, $variables);

        $ogDescriptionTemplate = $post->seoMeta?->og_description
            ?: get_setting('seo', 'post_social_seo_description')
                ?: $description;

        $ogDescription = $this->seoTemplateParser->parse($ogDescriptionTemplate, $variables);

        $xTitleTemplate = $post->seoMeta?->x_title;
        $xTitle = filled($xTitleTemplate)
            ? $this->seoTemplateParser->parse($xTitleTemplate, $variables)
            : null;

        $xDescriptionTemplate = $post->seoMeta?->x_description;
        $xDescription = filled($xDescriptionTemplate)
            ? $this->seoTemplateParser->parse($xDescriptionTemplate, $variables)
            : null;

        $ogImage = $post->seoMeta?->ogImage ?: $post->picture;

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $post->seoMeta?->canonical_url,
            fallbackUrl: $post->url,
        );

        $publishedTime = $post->published_at?->utc()->toIso8601String();
        $modifiedTime = $this->resolveModifiedTime($post, $publishedTime);

        $author = $post->author;
        $articleContentForAnalysis = $this->resolveArticleContentForAnalysis($post);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $post->seoMeta?->robots ?: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $post->title,

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
            xImageUrl: $post->seoMeta?->xImage?->url,

            pageImageUrl: $post->picture?->url,
            pageImageWidth: $post->picture?->primaryVariant?->width,
            pageImageHeight: $post->picture?->primaryVariant?->height,
            pageImageCaption: $post->title,

            publishedTime: $publishedTime,
            modifiedTime: $modifiedTime,

            authorName: $author?->display_name,
            // نویسنده‌ی بدون نام کاربری صفحه‌ی نویسنده ندارد؛ لینک نمی‌سازیم تا صفحه خطا ندهد.
            authorUrl: $author && filled($author->username) ? front_route('author', $author) : null,
            authorIdentifier: $author?->id ? (string)$author->id : $author?->display_name,
            authorSameAs: $this->resolveAuthorSameAs($author),

            wordCount: $this->estimateWordCount($articleContentForAnalysis),
            commentCount: $post->approved_comments_count ?? 0,
            keywords: $this->resolveKeywords($post),
            articleSections: $this->resolveArticleSections($post),

            readingTimeLabel: $this->readingTimeEstimator->estimate($articleContentForAnalysis) . ' دقیقه',
        );
    }

    /**
     * @return array<int, string>
     */
    protected function resolveAuthorSameAs(?User $author): array
    {
        if (!$author) {
            return [];
        }

        $candidates = [
            $author->website_url ?? null,
            $author->linkedin_url ?? null,
            $author->twitter_url ?? null,
            $author->github_url ?? null,
            $author->telegram_url ?? null,
            $author->instagram_url ?? null,
        ];

        return array_values(array_filter(
            $candidates,
            static fn($value) => filled($value)
        ));
    }

    protected function resolveModifiedTime(Post $post, ?string $publishedTime): ?string
    {
        if (!$post->updated_at) {
            return $publishedTime;
        }

        if ($post->published_at && $post->updated_at <= $post->published_at) {
            return $publishedTime;
        }

        return $post->updated_at->utc()->toIso8601String();
    }

    protected function resolveKeywords(Post $post): array
    {
        return $post->tags
            ->pluck('title')
            ->filter(static fn($value) => filled($value))
            ->values()
            ->all();
    }

    protected function resolveArticleSections(Post $post): array
    {
        return $post->categories
            ->pluck('title')
            ->filter(static fn($value) => filled($value))
            ->values()
            ->all();
    }

    protected function estimateWordCount(string $content): int
    {
        return $this->textNormalizer->wordCount($content);
    }

    protected function resolveExcerpt(Post $post): string
    {
        if (method_exists($post, 'excerpt')) {
            return (string)$post->excerpt();
        }

        return (string)($post->excerpt ?? '');
    }

    protected function resolveMainContent(Post $post): string
    {
        if (isset($post->body) && is_string($post->body)) {
            return $post->body;
        }

        return (string)($post->content ?? '');
    }

    protected function resolveArticleContentForAnalysis(Post $post): string
    {
        $content = $this->resolveMainContent($post);

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
