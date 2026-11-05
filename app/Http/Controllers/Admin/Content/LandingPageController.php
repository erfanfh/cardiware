<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\LandingPage;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * لندینگ‌ساز: ساخت صفحه‌ی لندینگ دلخواه با تم و بلوک‌های قابل چیدمان.
 */
final class LandingPageController extends Controller
{
    private const GATE = 'setting.product-landings';

    public function index(): View
    {
        Gate::authorize(self::GATE);

        return view('admin.content.landing-pages.index', [
            'pages' => LandingPage::query()->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        Gate::authorize(self::GATE);

        return view('admin.content.landing-pages.form', [
            'page' => new LandingPage([
                'status' => 'draft',
                'theme' => ['preset' => 'pgl', 'mode' => 'dark', 'pattern' => 'none'],
                'blocks' => [
                    ['type' => 'hero', 'title' => 'عنوان لندینگ', 'subtitle' => 'یک جمله‌ی جذاب درباره پیشنهاد', 'button_text' => 'خرید', 'button_link' => '#'],
                ],
                'seo' => [],
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize(self::GATE);

        $page = LandingPage::query()->create($this->validated($request) + ['created_by_user_id' => $request->user()->getKey()]);

        return redirect()->route('admin.landing-pages.edit', $page)->with('success', 'لندینگ ساخته شد.');
    }

    public function edit(LandingPage $landingPage): View
    {
        Gate::authorize(self::GATE);

        return view('admin.content.landing-pages.form', ['page' => $landingPage]);
    }

    public function update(Request $request, LandingPage $landingPage): RedirectResponse
    {
        Gate::authorize(self::GATE);

        $landingPage->update($this->validated($request, $landingPage));

        return back()->with('success', 'لندینگ ذخیره شد.');
    }

    public function destroy(LandingPage $landingPage): RedirectResponse
    {
        Gate::authorize(self::GATE);
        $landingPage->delete();

        return redirect()->route('admin.landing-pages.index')->with('success', 'لندینگ حذف شد.');
    }

    public function duplicate(LandingPage $landingPage): RedirectResponse
    {
        Gate::authorize(self::GATE);

        $copy = $landingPage->replicate(['views_count']);
        $copy->title = $landingPage->title . ' (کپی)';
        $copy->slug = $this->uniqueSlug($landingPage->slug . '-copy');
        $copy->status = 'draft';
        $copy->save();

        return redirect()->route('admin.landing-pages.edit', $copy)->with('success', 'یک کپی پیش‌نویس ساخته شد.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?LandingPage $page = null): array
    {
        $request->merge(['slug' => trim((string) $request->input('slug')) ?: null]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => [
                'nullable', 'string', 'max:190', 'regex:/^[\pL\pN]+(?:-[\pL\pN]+)*$/u',
                Rule::unique('landing_pages', 'slug')->ignore($page?->getKey()),
            ],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'theme' => ['nullable', 'array'],
            'theme.preset' => ['required', Rule::in(array_keys(LandingPage::THEME_PRESETS))],
            'theme.primary' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.accent' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme.mode' => ['nullable', Rule::in(['dark', 'light'])],
            'theme.pattern' => ['nullable', Rule::in(['none', 'dots', 'grid', 'diag'])],
            'theme.bg_image' => ['nullable', 'array'],
            'seo' => ['nullable', 'array'],
            'seo.meta_title' => ['nullable', 'string', 'max:190'],
            'seo.meta_description' => ['nullable', 'string', 'max:400'],
            'seo.og_image' => ['nullable', 'array'],
            'seo.noindex' => ['nullable', 'boolean'],
            'blocks' => ['nullable', 'array', 'max:40'],
            'blocks.*.type' => ['required', Rule::in(array_keys(LandingPage::BLOCK_TYPES))],
            'blocks.*.variant' => ['nullable', Rule::in(['default', 'accent', 'contrast'])],
            'blocks.*.title' => ['nullable', 'string', 'max:190'],
            'blocks.*.subtitle' => ['nullable', 'string', 'max:300'],
            'blocks.*.body' => ['nullable', 'string', 'max:5000'],
            'blocks.*.image' => ['nullable', 'array'],
            'blocks.*.button_text' => ['nullable', 'string', 'max:60'],
            'blocks.*.button_link' => ['nullable', 'string', 'max:500'],
            'blocks.*.button2_text' => ['nullable', 'string', 'max:60'],
            'blocks.*.button2_link' => ['nullable', 'string', 'max:500'],
            'blocks.*.items_text' => ['nullable', 'string', 'max:8000'],
            'blocks.*.product_ids' => ['nullable', 'string', 'max:300'],
            'blocks.*.video_url' => ['nullable', 'string', 'max:500'],
            'blocks.*.countdown_at' => ['nullable', 'date'],
            'blocks.*.coupon_code' => ['nullable', 'string', 'max:60'],
        ]);

        $blocks = collect($data['blocks'] ?? [])
            ->filter(fn ($block) => is_array($block))
            ->map(fn (array $block) => array_filter([
                'type' => $block['type'],
                'variant' => $block['variant'] ?? 'default',
                'title' => $block['title'] ?? null,
                'subtitle' => $block['subtitle'] ?? null,
                'body' => $block['body'] ?? null,
                'image' => ['id' => data_get($block, 'image.id'), 'path' => LandingPage::safeUrl(data_get($block, 'image.path'))],
                'button_text' => $block['button_text'] ?? null,
                'button_link' => LandingPage::safeUrl($block['button_link'] ?? null),
                'button2_text' => $block['button2_text'] ?? null,
                'button2_link' => LandingPage::safeUrl($block['button2_link'] ?? null),
                'items_text' => $block['items_text'] ?? null,
                'product_ids' => $block['product_ids'] ?? null,
                'video_url' => $block['video_url'] ?? null,
                'countdown_at' => $block['countdown_at'] ?? null,
                'coupon_code' => $block['coupon_code'] ?? null,
            ], fn ($value) => $value !== null))
            ->values()
            ->all();

        $theme = $data['theme'] ?? [];

        return [
            'title' => $data['title'],
            'slug' => $data['slug'] ?? $this->uniqueSlug(Str::slug($data['title']) ?: 'landing', $page),
            'status' => $data['status'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'theme' => [
                'preset' => $theme['preset'],
                'primary' => $theme['primary'] ?? null,
                'accent' => $theme['accent'] ?? null,
                'mode' => $theme['mode'] ?? 'dark',
                'pattern' => $theme['pattern'] ?? 'none',
                'bg_image' => ['id' => data_get($theme, 'bg_image.id'), 'path' => LandingPage::safeUrl(data_get($theme, 'bg_image.path'))],
            ],
            'seo' => [
                'meta_title' => data_get($data, 'seo.meta_title'),
                'meta_description' => data_get($data, 'seo.meta_description'),
                'og_image' => ['id' => data_get($data, 'seo.og_image.id'), 'path' => LandingPage::safeUrl(data_get($data, 'seo.og_image.path'))],
                'noindex' => (bool) data_get($data, 'seo.noindex', false),
            ],
            'blocks' => $blocks,
        ];
    }

    private function uniqueSlug(string $base, ?LandingPage $ignore = null): string
    {
        $slug = $base;
        $n = 2;
        while (LandingPage::query()->where('slug', $slug)->when($ignore, fn ($q) => $q->whereKeyNot($ignore->getKey()))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }
}
