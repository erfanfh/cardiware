<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\AuthorSeoVariableDataBuilder;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Str;

final class ResolveAuthorSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction         $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction    $resolveCanonicalUrlAction,
        protected SeoTemplateParser            $seoTemplateParser,
        protected AuthorSeoVariableDataBuilder $authorSeoVariableDataBuilder,
    )
    {
    }

    public function execute(User $author): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->authorSeoVariableDataBuilder->build($author);

        $authorName = $this->resolveAuthorName($author);
        $siteName = (string)$defaults['site_name'];

        $titleTemplate = '{author_name}، نویسنده در {site_title}';
        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);

        $description = $this->resolveDescription($author, $siteName);

        $ogTitle = $authorName;
        $ogDescription = $description;

        $image = $this->resolvePrimaryImage($author);

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $this->resolveCurrentPageUrl($author),
        );

        $sameAs = array_merge([$defaults['site_url']], $this->resolveSameAs($author));

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: $title,

            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],

            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: $authorName,

            personName: $authorName,
            personUrl: $canonicalUrl,
            sameAs: $sameAs,
        );
    }

    protected function resolveAuthorName(User $author): string
    {
        return (string)(
            $author->name
            ?? $author->display_name
            ?? $author->full_name
            ?? $author->username
            ?? $author->email
            ?? 'نویسنده'
        );
    }

    protected function resolveDescription(User $author, string $siteName): string
    {
        $bio = $author->bio
            ?? $author->about
            ?? $author->description
            ?? null;

        if (filled($bio)) {
            return Str::limit(trim(strip_tags((string)$bio)), 170, '');
        }

        return sprintf('آرشیو نوشته‌های %s در %s', $this->resolveAuthorName($author), $siteName);
    }

    protected function resolvePrimaryImage(User $author): ?MediaFile
    {
        $directImage = $author->avatar ?? null;

        if ($directImage instanceof MediaFile) {
            return $directImage;
        }

        if (method_exists($author, 'posts')) {
            $post = $author->posts()
                ->with(['picture.primaryVariant'])
                ->latest('published_at')
                ->first();

            return $post?->picture;
        }

        return null;
    }

    protected function resolveCurrentPageUrl(User $author): string
    {
        $baseUrl = front_route('author', $author);
        $page = max(1, (int)request()->integer('page', 1));

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . '?page=' . $page;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveSameAs(User $author): array
    {
        $candidates = [
            $author->website_url ?? null,
            $author->linkedin_url ?? null,
            $author->twitter_url ?? null,
            $author->github_url ?? null,
            $author->telegram_url ?? null,
        ];

        return array_values(array_filter($candidates, static fn($value) => filled($value)));
    }
}
