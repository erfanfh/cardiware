<?php

namespace App\Http\Controllers\Front\Content;

use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\LandingPage;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * نمایش لندینگ ساخته‌شده در لندینگ‌ساز. پیش‌نویس و بیرون از بازه‌ی نمایش ۴۰۴ می‌دهد،
 * مگر برای مدیری که از پنل «پیش‌نمایش» را زده است.
 */
final class LandingPageController extends Controller
{
    public function __invoke(Request $request, string $landingSlug): View
    {
        $page = LandingPage::query()->where('slug', $landingSlug)->firstOrFail();
        $isPreview = $request->boolean('preview') && Gate::allows('setting.product-landings');

        abort_unless($page->isPubliclyVisible() || $isPreview, 404);

        if (! $isPreview) {
            LandingPage::query()->whereKey($page->getKey())->increment('views_count');
        }

        // محصولات همه‌ی بلوک‌های «نمایش محصولات» با یک کوئری
        $productIds = collect($page->blocks ?? [])
            ->where('type', 'products')
            ->flatMap(fn (array $block) => preg_split('/[\s,،]+/u', (string) ($block['product_ids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique();
        $products = $productIds->isEmpty()
            ? collect()
            : Product::query()->whereIn('id', $productIds)->get()
                ->filter(fn (Product $product) => $product->isPubliclyVisible())
                ->keyBy('id');

        return view('front.landing-pages.show', [
            'page' => $page,
            'theme' => $page->resolvedTheme(),
            'products' => $products,
            'isPreview' => $isPreview,
        ]);
    }
}
