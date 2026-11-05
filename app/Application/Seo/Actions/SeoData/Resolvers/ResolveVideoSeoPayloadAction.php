<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\VideoSeoVariableDataBuilder;
use App\Domain\Identity\Models\User;
use App\Domain\Video\Models\Video;
use Illuminate\Support\Str;

final class ResolveVideoSeoPayloadAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $defaultsAction,
        private readonly ResolveCanonicalUrlAction $canonicalUrlAction,
        private readonly SeoTemplateParser $templateParser,
        private readonly VideoSeoVariableDataBuilder $variableBuilder,
    ) {}

    public function execute(Video $video): ResolvedSeoPayloadDTO
    {
        $defaults = $this->defaultsAction->execute();
        $variables = $this->variableBuilder->build($video);
        $headline = trim((string) $video->title) ?: 'ویدیو';

        $titleTemplate = $video->seoMeta?->title
            ?: get_setting('seo', 'video_seo_title')
            ?: '{video_title} {separator} {site_title}';
        $title = $this->templateParser->parse($titleTemplate, $variables);

        $fallbackDescription = $this->fallbackDescription($video, $headline);
        $descriptionTemplate = $video->seoMeta?->description
            ?: get_setting('seo', 'video_seo_description')
            ?: $fallbackDescription;
        $description = $this->templateParser->parse($descriptionTemplate, $variables);

        $ogTitle = $this->templateParser->parse(
            $video->seoMeta?->og_title ?: get_setting('seo', 'video_social_seo_title') ?: $title,
            $variables,
        );
        $ogDescription = $this->templateParser->parse(
            $video->seoMeta?->og_description ?: get_setting('seo', 'video_social_seo_description') ?: $description,
            $variables,
        );

        $xTitle = filled($video->seoMeta?->x_title)
            ? $this->templateParser->parse($video->seoMeta->x_title, $variables)
            : $ogTitle;
        $xDescription = filled($video->seoMeta?->x_description)
            ? $this->templateParser->parse($video->seoMeta->x_description, $variables)
            : $ogDescription;

        $thumbnail = $video->seoMeta?->ogImage ?: $video->cover;
        $author = $video->author;
        $published = ($video->published_at ?: $video->created_at)?->utc()->toIso8601String();

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $this->canonicalUrlAction->execute(
                customCanonicalUrl: $video->seoMeta?->canonical_url,
                fallbackUrl: $video->url,
            ),
            robots: $video->seoMeta?->robots ?: $defaults['robots'],
            title: $title,
            description: $description,
            headline: $headline,
            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: [
                'url' => $thumbnail?->url,
                'width' => $thumbnail?->primaryVariant?->width,
                'height' => $thumbnail?->primaryVariant?->height,
                'type' => $thumbnail?->primaryVariant?->mime_type,
            ],
            xTitle: $xTitle,
            xDescription: $xDescription,
            xImageUrl: $video->seoMeta?->xImage?->url ?: $thumbnail?->url,
            pageImageUrl: $thumbnail?->url,
            pageImageWidth: $thumbnail?->primaryVariant?->width,
            pageImageHeight: $thumbnail?->primaryVariant?->height,
            pageImageCaption: $headline,
            publishedTime: $published,
            modifiedTime: $this->modifiedTime($video, $published),
            authorName: $author?->display_name,
            // نویسنده‌ی بدون نام کاربری صفحه‌ی نویسنده ندارد؛ لینک نمی‌سازیم تا صفحه خطا ندهد.
            authorUrl: $author && filled($author->username) ? front_route('author', $author) : null,
            authorIdentifier: $author?->id ? (string) $author->id : $author?->display_name,
            authorSameAs: $this->authorSameAs($author),
            commentCount: $video->approved_comments_count ?? 0,
            keywords: $video->tags->pluck('title')->filter()->values()->all(),
            articleSections: $video->categories->pluck('title')->filter()->values()->all(),
        );
    }

    private function fallbackDescription(Video $video, string $headline): string
    {
        $text = trim(strip_tags((string) ($video->excerpt ?: $video->body)));

        return filled($text)
            ? Str::limit($text, 160, '…')
            : Str::limit('تماشای ویدیوی «'.$headline.'» در مرکز ویدیوی گیمینگ مودینگ‌وی.', 160, '…');
    }

    private function modifiedTime(Video $video, ?string $published): ?string
    {
        if (! $video->updated_at || ($video->published_at && $video->updated_at <= $video->published_at)) {
            return $published;
        }

        return $video->updated_at->utc()->toIso8601String();
    }

    /** @return array<int, string> */
    private function authorSameAs(?User $author): array
    {
        if (! $author) {
            return [];
        }

        return array_values(array_filter([
            $author->website_url ?? null,
            $author->linkedin_url ?? null,
            $author->twitter_url ?? null,
            $author->github_url ?? null,
            $author->telegram_url ?? null,
            $author->instagram_url ?? null,
        ], static fn ($url) => filled($url)));
    }
}
