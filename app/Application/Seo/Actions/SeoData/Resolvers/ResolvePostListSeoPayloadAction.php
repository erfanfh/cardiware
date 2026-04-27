<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\PostListSeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;

final class ResolvePostListSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction           $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction      $resolveCanonicalUrlAction,
        protected SeoTemplateParser              $seoTemplateParser,
        protected PostListSeoVariableDataBuilder $postListSeoVariableDataBuilder,
    )
    {
    }

    public function execute(): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->postListSeoVariableDataBuilder->build();

        $titleTemplate = get_setting('seo', 'post_list_seo_title')
            ?: 'نوشته ها {separator} {site_title}';

        $descriptionTemplate = get_setting('seo', 'post_list_seo_description')
            ?: 'جدیدترین نوشته‌ها، آموزش‌ها، اخبار و مطالب منتشر شده در {site_title} را بخوانید.';

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $image = $this->resolvePrimaryImage();

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $this->resolveCurrentPageUrl(),
        );

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: 'نوشته‌ها',

            ogTitle: $title,
            ogDescription: $description,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],

            xTitle: $title,
            xDescription: $description,
            xImageUrl: $image?->url,

            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: 'نوشته‌ها',
        );
    }

    protected function resolveCurrentPageUrl(): string
    {
        $baseUrl = front_route('posts.list');
        $page = max(1, (int)request()->integer('page', 1));

        if ($page <= 1) {
            return $baseUrl;
        }

        return $baseUrl . '?page=' . $page;
    }

    protected function resolvePrimaryImage(): ?MediaFile
    {
        $imageSetting = get_setting('seo', 'post_list_og_image');

        if ($imageSetting?->id) {
            return MediaFile::with('primaryVariant')->find($imageSetting->id);
        }

        $fallbackImage = get_setting('seo', 'default_og_image');

        if ($fallbackImage?->id) {
            return MediaFile::with('primaryVariant')->find($fallbackImage->id);
        }

        return null;
    }
}
