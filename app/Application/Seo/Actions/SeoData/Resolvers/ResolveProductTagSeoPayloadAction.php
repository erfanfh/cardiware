<?php

namespace App\Application\Seo\Actions\SeoData\Resolvers;

use App\Application\Seo\Actions\Canonical\ResolveCanonicalUrlAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\Support\SeoTemplateParser;
use App\Application\Seo\Support\VariableBuilders\TaxonomySeoVariableDataBuilder;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Str;

final class ResolveProductTagSeoPayloadAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
        private readonly ResolveCanonicalUrlAction $resolveCanonicalUrlAction,
        private readonly SeoTemplateParser $seoTemplateParser,
        private readonly TaxonomySeoVariableDataBuilder $taxonomySeoVariableDataBuilder,
    ) {}

    public function execute(TaxonomyTerm $tag): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->taxonomySeoVariableDataBuilder->build($tag);
        $tag->loadMissing(['seoMeta.ogImage.primaryVariant', 'seoMeta.xImage']);

        $titleTemplate = $tag->seoMeta?->title
            ?: 'خرید و دانلود محصولات با برچسب {taxonomy_title} {separator} {site_title}';
        $title = $this->appendPaginationToTitle(
            $this->seoTemplateParser->parse($titleTemplate, $variables),
        );

        $descriptionTemplate = $tag->seoMeta?->description
            ?: $this->fallbackDescription($tag);
        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitle = $this->seoTemplateParser->parse(
            $tag->seoMeta?->og_title ?: $title,
            $variables,
        );
        $ogDescription = $this->seoTemplateParser->parse(
            $tag->seoMeta?->og_description ?: $description,
            $variables,
        );
        $image = $tag->seoMeta?->ogImage ?: $this->resolveFallbackImage($tag);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $this->canonicalUrl($tag),
            robots: $this->robots($tag, $defaults['robots']),
            title: $title,
            description: $description,
            headline: $tag->title,
            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],
            xTitle: $tag->seoMeta?->x_title
                ? $this->seoTemplateParser->parse($tag->seoMeta->x_title, $variables)
                : $ogTitle,
            xDescription: $tag->seoMeta?->x_description
                ? $this->seoTemplateParser->parse($tag->seoMeta->x_description, $variables)
                : $ogDescription,
            xImageUrl: $tag->seoMeta?->xImage?->url ?: $image?->url,
            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: $tag->title,
            keywords: [$tag->title],
        );
    }

    private function fallbackDescription(TaxonomyTerm $tag): string
    {
        $description = trim(strip_tags(html_entity_decode((string) $tag->description)));

        if ($description !== '') {
            return Str::limit($description, 170, '');
        }

        return 'جدیدترین محصولات مرتبط با برچسب «'.$tag->title.'» را در Cardiware مشاهده، مقایسه و انتخاب کنید.';
    }

    private function resolveFallbackImage(TaxonomyTerm $tag): ?MediaFile
    {
        $paginator = request()->attributes->get('seo_paginator');

        foreach ($paginator?->items() ?? [] as $product) {
            if ($product->picture instanceof MediaFile) {
                return $product->picture;
            }
        }

        return $tag->products()
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('saleItems')
            ->with('picture.primaryVariant')
            ->latest('published_at')
            ->first()?->picture;
    }

    private function canonicalUrl(TaxonomyTerm $tag): string
    {
        $currentPageUrl = $this->paginatedUrl($tag);

        if ($this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $tag->seoMeta?->canonical_url,
            fallbackUrl: $currentPageUrl,
        );
    }

    private function paginatedUrl(TaxonomyTerm $tag): string
    {
        if ($this->currentPage() === 1) {
            return front_route('products.tags', ['path' => $tag->path]);
        }

        return front_route('products.tags.page', [
            'path' => $tag->path,
            'page' => $this->currentPage(),
        ]);
    }

    private function robots(TaxonomyTerm $tag, string $default): string
    {
        if (request()->hasAny(['categories', 'priceMin', 'priceMax', 'sort'])) {
            return 'noindex, follow';
        }

        return $tag->seoMeta?->robots ?: $default;
    }

    private function appendPaginationToTitle(string $title): string
    {
        if ($this->currentPage() === 1) {
            return $title;
        }

        $lastPage = request()->attributes->get('seo_paginator')?->lastPage();
        $pagination = $lastPage
            ? "صفحه {$this->currentPage()} از {$lastPage}"
            : "صفحه {$this->currentPage()}";

        $parts = explode(' | ', $title);

        if (count($parts) >= 2) {
            $siteName = array_pop($parts);

            return implode(' | ', $parts).' | '.$pagination.' | '.$siteName;
        }

        return $title.' | '.$pagination;
    }

    private function currentPage(): int
    {
        return max(1, (int) request()->route('page', 1));
    }
}
