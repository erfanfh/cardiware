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

final class ResolveProductCategorySeoPayloadAction
{
    public function __construct(
        private readonly GetSeoDefaultsAction $getSeoDefaultsAction,
        private readonly ResolveCanonicalUrlAction $resolveCanonicalUrlAction,
        private readonly SeoTemplateParser $seoTemplateParser,
        private readonly TaxonomySeoVariableDataBuilder $taxonomySeoVariableDataBuilder,
    ) {}

    public function execute(TaxonomyTerm $category): ResolvedSeoPayloadDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $variables = $this->taxonomySeoVariableDataBuilder->build($category);
        $category->loadMissing(['seoMeta.ogImage.primaryVariant', 'seoMeta.xImage']);

        $titleTemplate = $category->seoMeta?->title
            ?: 'خرید و دانلود {taxonomy_title} {separator} {site_title}';
        $title = $this->appendPaginationToTitle(
            $this->seoTemplateParser->parse($titleTemplate, $variables),
        );

        $descriptionTemplate = $category->seoMeta?->description
            ?: $this->fallbackDescription($category);
        $description = $this->seoTemplateParser->parse($descriptionTemplate, $variables);

        $ogTitle = $this->seoTemplateParser->parse(
            $category->seoMeta?->og_title ?: $title,
            $variables,
        );
        $ogDescription = $this->seoTemplateParser->parse(
            $category->seoMeta?->og_description ?: $description,
            $variables,
        );
        $image = $category->seoMeta?->ogImage ?: $this->resolveFallbackImage($category);

        return new ResolvedSeoPayloadDTO(
            canonicalUrl: $this->canonicalUrl($category),
            robots: $this->robots($category, $defaults['robots']),
            title: $title,
            description: $description,
            headline: $category->title,
            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: [
                'url' => $image?->url,
                'width' => $image?->primaryVariant?->width,
                'height' => $image?->primaryVariant?->height,
                'type' => $image?->primaryVariant?->mime_type,
            ],
            xTitle: $category->seoMeta?->x_title
                ? $this->seoTemplateParser->parse($category->seoMeta->x_title, $variables)
                : $ogTitle,
            xDescription: $category->seoMeta?->x_description
                ? $this->seoTemplateParser->parse($category->seoMeta->x_description, $variables)
                : $ogDescription,
            xImageUrl: $category->seoMeta?->xImage?->url ?: $image?->url,
            pageImageUrl: $image?->url,
            pageImageWidth: $image?->primaryVariant?->width,
            pageImageHeight: $image?->primaryVariant?->height,
            pageImageCaption: $category->title,
            keywords: [$category->title],
        );
    }

    private function fallbackDescription(TaxonomyTerm $category): string
    {
        $description = trim(strip_tags(html_entity_decode((string) $category->description)));

        if ($description !== '') {
            return Str::limit($description, 170, '');
        }

        return 'جدیدترین محصولات دسته «'.$category->title.'» را در Cardiware مشاهده، مقایسه و انتخاب کنید.';
    }

    private function resolveFallbackImage(TaxonomyTerm $category): ?MediaFile
    {
        $paginator = request()->attributes->get('seo_paginator');

        foreach ($paginator?->items() ?? [] as $product) {
            if ($product->picture instanceof MediaFile) {
                return $product->picture;
            }
        }

        return $category->productsIncludingDescendants()
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('saleItems')
            ->with('picture.primaryVariant')
            ->latest('published_at')
            ->first()?->picture;
    }

    private function canonicalUrl(TaxonomyTerm $category): string
    {
        $currentPageUrl = $this->paginatedUrl($category);

        if ($this->currentPage() > 1) {
            return $currentPageUrl;
        }

        return $this->resolveCanonicalUrlAction->execute(
            customCanonicalUrl: $category->seoMeta?->canonical_url,
            fallbackUrl: $currentPageUrl,
        );
    }

    private function paginatedUrl(TaxonomyTerm $category): string
    {
        if ($this->currentPage() === 1) {
            return front_route('products.categories', ['path' => $category->path]);
        }

        return front_route('products.categories.page', [
            'path' => $category->path,
            'page' => $this->currentPage(),
        ]);
    }

    private function robots(TaxonomyTerm $category, string $default): string
    {
        if (request()->hasAny(['categories', 'priceMin', 'priceMax', 'sort'])) {
            return 'noindex, follow';
        }

        return $category->seoMeta?->robots ?: $default;
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
