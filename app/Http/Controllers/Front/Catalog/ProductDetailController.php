<?php

namespace App\Http\Controllers\Front\Catalog;

use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Catalog\Models\Product;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;

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
        $product = $resolvedSlug->model;


        return view('front.catalog.products.detail', compact('product'));
    }
}
