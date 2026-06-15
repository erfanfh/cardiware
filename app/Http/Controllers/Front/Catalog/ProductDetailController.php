<?php

namespace App\Http\Controllers\Front\Catalog;

use App\Application\Commerce\Reports\ProductSalesReport;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class ProductDetailController extends Controller
{
    public function __invoke(string $productSlug, ResolveSlugAction $resolveSlugAction)
    {
        $resolvedSlug = $resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: SlugScopeEnum::PRODUCT,
                value: $productSlug,
            )
        );

        if (!$resolvedSlug->found) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var Product $product */
        $product = $resolvedSlug->model->load([
            'categories',
            'tags',
            'picture.primaryVariant',
            'imageGallery.mediaFile.primaryVariant',
            'requirements',
            'installationSteps',
            'faqs',
            'releases.releaseChanges',
            'informationEntries.field',
        ]);

        $salesSummary = app(ProductSalesReport::class)->saleItemSummary($product->id);
        $soldQuantity = $salesSummary['sold_quantity'];

        $relatedProducts = $this->getRelatedProducts($product);

        app(StoreViewAction::class)->execute($product);

        return view('front.catalog.products.detail', compact('product', 'soldQuantity', 'relatedProducts'));
    }

    private function getRelatedProducts(Product $product): Collection
    {
        $tagIds = $product->tags->pluck('id')->filter()->values()->toArray();
        $categoryIds = $product->categories->pluck('id')->filter()->values()->toArray();
        $taxonomyIds = array_merge($tagIds, $categoryIds);

        return Product::query()
            ->whereHas('taxonomyTerms', function ($builder) use ($taxonomyIds) {
                $builder->whereIn('taxonomy_terms.id', $taxonomyIds);
            })
            ->whereNot('id', $product->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }
}
