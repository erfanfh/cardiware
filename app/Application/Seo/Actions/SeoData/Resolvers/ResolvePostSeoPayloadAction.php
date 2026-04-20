<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedPostSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostSeoVariableDataBuilder;
use App\Domain\Content\Models\Post;
use App\Infrastructure\Support\ReadingTimeEstimator;

final class ResolvePostSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction       $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction  $resolveCanonicalUrlAction,
        protected SeoTemplateParser          $seoTemplateParser,
        protected PostSeoVariableDataBuilder $postSeoVariableDataBuilder,
        protected ReadingTimeEstimator       $readingTimeEstimator,
    )
    {
    }

    public function execute(Post $post): ResolvedPostSeoPayloadDTO
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

        return new ResolvedPostSeoPayloadDTO(
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

            publishedTime: $publishedTime,
            modifiedTime: $modifiedTime,

            authorName: $author?->display_name,
            authorUrl: $author
                ? rtrim(route('author', ['username' => $author->username]), '/') . '/'
                : null,
            authorIdentifier: $author?->id
                ? (string)$author->id
                : $author?->display_name,
            authorSameAs: [url('/')],

            pageImageUrl: $post->picture?->url,
            pageImageWidth: $post->picture?->primaryVariant?->width,
            pageImageHeight: $post->picture?->primaryVariant?->height,
            pageImageCaption: $post->title,

            wordCount: $this->estimateWordCount($post),
            commentCount: $post->approved_comments_count ?? 0,
            keywords: $this->resolveKeywords($post),
            articleSections: $this->resolveArticleSections($post),

            readingTimeLabel: $this->readingTimeEstimator->estimate($this->resolveMainContent($post)) . ' دقیقه',
        );
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

    /**
     * @return array<int, string>
     */
    protected function resolveKeywords(Post $post): array
    {
        return $post->tags
            ->pluck('title')
            ->filter(static fn($value) => filled($value))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function resolveArticleSections(Post $post): array
    {
        return $post->categories
            ->pluck('title')
            ->filter(static fn($value) => filled($value))
            ->values()
            ->all();
    }

    protected function estimateWordCount(Post $post): int
    {
        $text = strip_tags($this->resolveMainContent($post));
        $text = preg_replace('/\s+/u', ' ', trim($text));

        if (blank($text)) {
            return 0;
        }

        return count(preg_split('/\s+/u', $text) ?: []);
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
}
