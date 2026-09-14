<?php

namespace App\Http\Controllers\Front\Catalog;

use App\Application\Catalog\Actions\GetProductDetailAction;
use App\Application\Catalog\Actions\GetProductDetailStatsAction;
use App\Application\Catalog\Actions\GetRelatedProductsAction;
use App\Application\Discussion\Actions\GetApprovedCommentsCountAction;
use App\Application\Discussion\Actions\GetCommentableRatingSummaryAction;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ProductDetailController extends Controller
{
    public function __invoke(
        string                            $productSlug,
        ResolveSlugAction                 $resolveSlugAction,
        GetProductDetailAction            $getProductDetailAction,
        GetProductDetailStatsAction       $getProductDetailStatsAction,
        GetRelatedProductsAction          $getRelatedProductsAction,
        GetCommentableRatingSummaryAction $getCommentableRatingSummaryAction,
        StoreViewAction                   $storeViewAction,
    ): View|RedirectResponse
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

        $product = $getProductDetailAction->execute($resolvedSlug->model);

        $ratingSummary = $getCommentableRatingSummaryAction->execute($product);
        $averageRating = $product->displaySetting->show_average_rating
            ? $ratingSummary->averageRating
            : null;

        $stats = $getProductDetailStatsAction->execute($product);

        $relatedProducts = $getRelatedProductsAction->execute($product);

        $commentsCount = app(GetApprovedCommentsCountAction::class)->execute($product);

        $storeViewAction->execute($product);

        return view('front.catalog.products.detail', [
            'product' => $product,
            'soldQuantity' => $stats['sold_quantity'],
            'storeSoldQuantity' => $stats['store_sold_quantity'],
            'relatedProducts' => $relatedProducts,
            'storeProducts' => $stats['store_products_count'],
            'commentsCount' => $commentsCount,
            'averageRating' => $averageRating,
            'ratingSummary' => $ratingSummary,
        ]);
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
