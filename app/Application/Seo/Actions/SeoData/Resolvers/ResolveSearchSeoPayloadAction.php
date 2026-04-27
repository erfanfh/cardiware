<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\SearchSeoVariableDataBuilder;
use App\Domain\Media\Models\MediaFile;

final class ResolveSearchSeoPayloadAction
{
    public function __construct(
        protected GetSeoDefaultsAction         $getSeoDefaultsAction,
        protected ResolveCanonicalUrlAction    $resolveCanonicalUrlAction,
        protected SeoTemplateParser            $seoTemplateParser,
        protected SearchSeoVariableDataBuilder $searchSeoVariableDataBuilder,
    )
    {
    }

    public function execute(): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();

        $searchTerm = $this->resolveSearchTerm();
        $variables = $this->searchSeoVariableDataBuilder->build($searchTerm);

        $titleTemplate = get_setting('seo', 'search_seo_title')
            ?: 'شما برای {search_term} جستجو کردید {separator} {site_title}';

        $descriptionTemplate = get_setting('seo', 'search_seo_description')
            ?: 'نتایج جستجو برای {search_term} در {site_title} را مشاهده کنید.';

        $title = $this->seoTemplateParser->parse($titleTemplate, $variables);
        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $image = $this->resolvePrimaryImage();

        $canonicalUrl = $this->resolveCanonicalUrlAction->execute(
            fallbackUrl: $this->resolveCurrentPageUrl($searchTerm),
        );

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $canonicalUrl,
            robots: $defaults['robots'],

            title: $title,
            description: $description,
            headline: filled($searchTerm)
                ? 'نتایج جستجو برای ' . $searchTerm
                : 'نتایج جستجو',

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
            pageImageCaption: filled($searchTerm)
                ? 'نتایج جستجو برای ' . $searchTerm
                : 'نتایج جستجو',
        );
    }

    protected function resolveSearchTerm(): string
    {
        return trim((string)request()->query('q', request()->query('s', '')));
    }

    protected function resolveCurrentPageUrl(string $searchTerm): string
    {
        $query = [];

        if (filled($searchTerm)) {
            $query['q'] = $searchTerm;
        }

        $page = max(1, (int)request()->integer('page', 1));

        if ($page > 1) {
            $query['page'] = $page;
        }

        return front_route('search', $query);
    }

    protected function resolvePrimaryImage(): ?MediaFile
    {
        $imageSetting = get_setting('seo', 'search_og_image');

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
