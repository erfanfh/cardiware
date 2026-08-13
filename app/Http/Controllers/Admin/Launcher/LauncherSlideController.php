<?php

namespace App\Http\Controllers\Admin\Launcher;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Models\LauncherSlide;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class LauncherSlideController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('launcher-device.manage');

        $slides = LauncherSlide::query()
            ->with(['image.primaryVariant', 'targetSaleItem.product'])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
        $editingSlide = filled($request->query('edit'))
            ? LauncherSlide::query()->with('image.primaryVariant')->findOrFail((int)$request->query('edit'))
            : null;
        $saleItems = ProductSaleItem::query()
            ->where('launcher_enabled', true)
            ->with('product:id,title')
            ->orderBy('launcher_sort_order')
            ->orderByDesc('id')
            ->get(['id', 'product_id', 'title', 'launcher_title']);

        return view('admin.launcher.slides.index', compact('slides', 'editingSlide', 'saleItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');

        $validated = $request->validate([
            'id' => ['nullable', 'integer', Rule::exists('launcher_slides', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'button_text' => ['required', 'string', 'max:80'],
            'image.id' => ['required', 'integer', Rule::exists('media_files', 'id')],
            'target_type' => ['required', Rule::in(['product', 'url', 'none'])],
            'target_sale_item_id' => [
                'nullable',
                'required_if:target_type,product',
                'integer',
                Rule::exists('product_sale_items', 'id')->where('launcher_enabled', true),
            ],
            'target_url' => ['nullable', 'required_if:target_type,url', 'url:https', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_enabled' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ], [
            'image.id.required' => 'انتخاب تصویر اسلاید الزامی است.',
            'target_sale_item_id.required_if' => 'برای لینک داخلی، محصول مقصد را انتخاب کنید.',
            'target_url.required_if' => 'برای لینک سایت، آدرس HTTPS را وارد کنید.',
            'target_url.url' => 'لینک اسلاید باید کامل و با HTTPS باشد.',
            'ends_at.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
        ]);

        $slide = filled($validated['id'] ?? null)
            ? LauncherSlide::query()->findOrFail((int)$validated['id'])
            : new LauncherSlide();
        $targetType = (string)$validated['target_type'];
        $slide->fill([
            'title' => trim((string)$validated['title']),
            'description' => filled($validated['description'] ?? null)
                ? trim((string)$validated['description'])
                : null,
            'button_text' => trim((string)$validated['button_text']),
            'image_media_id' => (int)data_get($validated, 'image.id'),
            'target_type' => $targetType,
            'target_sale_item_id' => $targetType === 'product'
                ? (int)$validated['target_sale_item_id']
                : null,
            'target_url' => $targetType === 'url'
                ? trim((string)$validated['target_url'])
                : null,
            'sort_order' => (int)($validated['sort_order'] ?? 0),
            'is_enabled' => $request->boolean('is_enabled'),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ])->save();

        return redirect()->route('admin.launcher-slides.index')
            ->with('success', 'اسلاید لانچر با موفقیت ذخیره شد.')
            ->with('success_title', 'اسلایدر لانچر');
    }

    public function destroy(LauncherSlide $launcherSlide): RedirectResponse
    {
        Gate::authorize('launcher-device.manage');
        $launcherSlide->delete();

        return redirect()->route('admin.launcher-slides.index')
            ->with('success', 'اسلاید انتخاب‌شده حذف شد.')
            ->with('success_title', 'اسلایدر لانچر');
    }
}
