@extends('admin.layout')

@section('title', $page->exists ? 'ویرایش لندینگ: ' . $page->title : 'لندینگ جدید')

@section('content')
    @php
        use App\Domain\Content\Models\LandingPage;
        $theme = old('theme', $page->theme ?? []);
        $seo = old('seo', $page->seo ?? []);
        $resolved = $page->resolvedTheme();
        $blocks = collect(old('blocks', $page->blocks ?? []))->map(fn ($block) => array_merge([
            'type' => 'text', 'variant' => 'default', 'title' => '', 'subtitle' => '', 'body' => '',
            'image' => ['id' => null, 'path' => null], 'button_text' => '', 'button_link' => '', 'button2_text' => '', 'button2_link' => '',
            'items_text' => '', 'product_ids' => '', 'video_url' => '', 'countdown_at' => '', 'coupon_code' => '',
        ], (array) $block))->values()->all();
        $typeOptions = collect(LandingPage::BLOCK_TYPES)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all();
        $dateValue = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i') : '';
    @endphp

    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ $page->exists ? route('admin.landing-pages.update', $page) : route('admin.landing-pages.store') }}" class="space-y-6">
            @csrf
            @if($page->exists) @method('PUT') @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.landing-pages.index') }}" class="text-xs font-bold text-slate-500">← همه لندینگ‌ها</a>
                <div class="flex gap-2">
                    @if($page->exists)
                        <a href="{{ route('landing-pages.show', ['landingSlug' => $page->slug, 'preview' => 1]) }}" target="_blank" class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-4 text-xs font-extrabold">پیش‌نمایش</a>
                    @endif
                    <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-brand-500 px-5 text-xs font-extrabold text-white hover:bg-brand-600">ذخیره لندینگ</button>
                </div>
            </div>

            <x-admin.metabox title="اطلاعات اصلی">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.field label="عنوان" required>
                        <x-admin.form.input name="title" :value="old('title', $page->title)"/>
                    </x-admin.field>
                    <x-admin.field label="نامک (آدرس)" hint="فقط حروف، عدد و خط تیره؛ خالی = از روی عنوان. آدرس: /landing/نامک">
                        <x-admin.form.input name="slug" :value="old('slug', $page->slug)" dir="auto"/>
                    </x-admin.field>
                    <x-admin.field label="وضعیت">
                        <x-admin.form.select name="status" :value="old('status', $page->status ?? 'draft')"
                                             :options="[['value' => 'draft', 'label' => 'پیش‌نویس (فقط پیش‌نمایش مدیر)'], ['value' => 'published', 'label' => 'منتشرشده']]"/>
                    </x-admin.field>
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.field label="شروع نمایش (اختیاری)">
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $dateValue($page->starts_at)) }}" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                        </x-admin.field>
                        <x-admin.field label="پایان نمایش (اختیاری)">
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $dateValue($page->ends_at)) }}" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                        </x-admin.field>
                    </div>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="تم و رنگ‌بندی">
                <div class="grid gap-4 md:grid-cols-3" x-data="{ preset: @js($theme['preset'] ?? 'pgl') }">
                    <x-admin.field label="تم آماده">
                        <select name="theme[preset]" x-model="preset" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                            @foreach(LandingPage::THEME_PRESETS as $key => $preset)
                                <option value="{{ $key }}">{{ $preset['label'] }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                    <x-admin.field label="پترن پس‌زمینه">
                        <x-admin.form.select name="theme[pattern]" :value="$theme['pattern'] ?? 'none'"
                                             :options="[['value' => 'none', 'label' => 'بدون پترن'], ['value' => 'dots', 'label' => 'نقطه‌ای'], ['value' => 'grid', 'label' => 'شبکه‌ای'], ['value' => 'diag', 'label' => 'مورب']]"/>
                    </x-admin.field>
                    <x-admin.field label="تصویر پس‌زمینه (اختیاری)">
                        <x-admin.form.media-picker mode="field" name="theme[bg_image]" :value="['id' => data_get($theme, 'bg_image.id'), 'path' => data_get($theme, 'bg_image.path')]"/>
                    </x-admin.field>
                    <div class="grid grid-cols-3 gap-3 md:col-span-3" x-show="preset === 'custom'" x-cloak>
                        <x-admin.field label="رنگ اصلی">
                            <input type="color" name="theme[primary]" value="{{ LandingPage::isColor($theme['primary'] ?? null) ? $theme['primary'] : $resolved['primary'] }}" class="h-11 w-full rounded-xl border border-slate-200">
                        </x-admin.field>
                        <x-admin.field label="رنگ دوم">
                            <input type="color" name="theme[accent]" value="{{ LandingPage::isColor($theme['accent'] ?? null) ? $theme['accent'] : $resolved['accent'] }}" class="h-11 w-full rounded-xl border border-slate-200">
                        </x-admin.field>
                        <x-admin.field label="حالت">
                            <x-admin.form.select name="theme[mode]" :value="$theme['mode'] ?? 'dark'" :options="[['value' => 'dark', 'label' => 'تیره'], ['value' => 'light', 'label' => 'روشن']]"/>
                        </x-admin.field>
                    </div>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="بلوک‌های صفحه">
                <div class="mb-4 rounded-xl bg-slate-50 p-3 text-xs leading-6 text-slate-500">
                    بلوک‌ها را اضافه کنید و با کشیدن مرتب کنید. «فهرست آیتم‌ها» هر خط یک آیتم است:
                    ویژگی‌ها: <code>🚀 | عنوان | توضیح</code> — پرسش‌ها: <code>سوال | جواب</code> — گالری: <code>آدرس تصویر | توضیح</code>.
                    شناسه محصولات را با ویرگول جدا کنید (مثلاً <code>12, 15, 18</code>).
                </div>
                <x-admin.form.repeater
                    name="blocks"
                    :items="$blocks"
                    title="بلوک‌ها"
                    addText="افزودن بلوک"
                    emptyText="هنوز بلوکی اضافه نشده است."
                    headingKey="title"
                    headingPlaceholder="بلوک بدون عنوان"
                    :defaultItem="[
                        'type' => 'text', 'variant' => 'default', 'title' => '', 'subtitle' => '', 'body' => '',
                        'image' => ['id' => null, 'path' => null], 'button_text' => '', 'button_link' => '', 'button2_text' => '', 'button2_link' => '',
                        'items_text' => '', 'product_ids' => '', 'video_url' => '', 'countdown_at' => '', 'coupon_code' => '',
                    ]"
                >
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
                        <x-admin.field label="نوع بلوک" class="lg:col-span-3">
                            <select x-bind:name="field(i, 'type')" x-model="it.type" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                                @foreach($typeOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </x-admin.field>
                        <x-admin.field label="پس‌زمینه بلوک" class="lg:col-span-3">
                            <select x-bind:name="field(i, 'variant')" x-model="it.variant" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                                <option value="default">ساده</option>
                                <option value="accent">رنگی ملایم</option>
                                <option value="contrast">رنگ اصلی (پررنگ)</option>
                            </select>
                        </x-admin.field>

                        <x-admin.field label="عنوان" class="lg:col-span-3">
                            <x-admin.form.input x-bind:name="field(i, 'title')" x-model="it.title"/>
                        </x-admin.field>
                        <x-admin.field label="زیرعنوان / برچسب" class="lg:col-span-3">
                            <x-admin.form.input x-bind:name="field(i, 'subtitle')" x-model="it.subtitle"/>
                        </x-admin.field>

                        <x-admin.field label="متن" class="lg:col-span-6" x-show="!['gallery', 'products'].includes(it.type)">
                            <textarea x-bind:name="field(i, 'body')" x-model="it.body" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                        </x-admin.field>

                        <x-admin.field label="تصویر" class="lg:col-span-6" x-show="['hero', 'image_text'].includes(it.type)">
                            <x-admin.form.media-picker mode="repeater" name="image"/>
                        </x-admin.field>

                        <x-admin.field label="فهرست آیتم‌ها (هر خط یک آیتم)" class="lg:col-span-6" x-show="['features', 'faq', 'gallery'].includes(it.type)">
                            <textarea x-bind:name="field(i, 'items_text')" x-model="it.items_text" rows="5" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                        </x-admin.field>

                        <x-admin.field label="شناسه محصولات" class="lg:col-span-6" x-show="it.type === 'products'">
                            <x-admin.form.input x-bind:name="field(i, 'product_ids')" x-model="it.product_ids" dir="ltr" placeholder="12, 15, 18"/>
                        </x-admin.field>

                        <x-admin.field label="لینک ویدیو (آپارات یا یوتیوب)" class="lg:col-span-6" x-show="it.type === 'video'">
                            <x-admin.form.input x-bind:name="field(i, 'video_url')" x-model="it.video_url" dir="ltr" placeholder="https://www.aparat.com/v/..."/>
                        </x-admin.field>

                        <x-admin.field label="زمان پایان شمارش معکوس" class="lg:col-span-3" x-show="['countdown', 'hero'].includes(it.type)">
                            <input type="datetime-local" x-bind:name="field(i, 'countdown_at')" x-model="it.countdown_at" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm">
                        </x-admin.field>

                        <x-admin.field label="کد تخفیف" class="lg:col-span-3" x-show="it.type === 'coupon'">
                            <x-admin.form.input x-bind:name="field(i, 'coupon_code')" x-model="it.coupon_code" dir="ltr"/>
                        </x-admin.field>

                        <div class="grid grid-cols-1 gap-4 lg:col-span-6 lg:grid-cols-4" x-show="!['features', 'faq', 'gallery', 'products', 'video', 'text'].includes(it.type)">
                            <x-admin.field label="متن دکمه اصلی">
                                <x-admin.form.input x-bind:name="field(i, 'button_text')" x-model="it.button_text"/>
                            </x-admin.field>
                            <x-admin.field label="لینک دکمه اصلی">
                                <x-admin.form.input x-bind:name="field(i, 'button_link')" x-model="it.button_link" dir="ltr" placeholder="/products/... یا https://..."/>
                            </x-admin.field>
                            <x-admin.field label="متن دکمه دوم">
                                <x-admin.form.input x-bind:name="field(i, 'button2_text')" x-model="it.button2_text"/>
                            </x-admin.field>
                            <x-admin.field label="لینک دکمه دوم">
                                <x-admin.form.input x-bind:name="field(i, 'button2_link')" x-model="it.button2_link" dir="ltr"/>
                            </x-admin.field>
                        </div>
                    </div>
                </x-admin.form.repeater>
            </x-admin.metabox>

            <x-admin.metabox title="سئو">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.field label="عنوان سئو (خالی = عنوان لندینگ)">
                        <x-admin.form.input name="seo[meta_title]" :value="$seo['meta_title'] ?? ''"/>
                    </x-admin.field>
                    <x-admin.field label="تصویر اشتراک‌گذاری (og:image)">
                        <x-admin.form.media-picker mode="field" name="seo[og_image]" :value="['id' => data_get($seo, 'og_image.id'), 'path' => data_get($seo, 'og_image.path')]"/>
                    </x-admin.field>
                    <x-admin.field label="توضیح سئو" class="md:col-span-2">
                        <textarea name="seo[meta_description]" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">{{ $seo['meta_description'] ?? '' }}</textarea>
                    </x-admin.field>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="seo[noindex]" value="0">
                        <input type="checkbox" name="seo[noindex]" value="1" @checked(!empty($seo['noindex']))>
                        در موتورهای جستجو نمایش داده نشود (noindex)
                    </label>
                </div>
            </x-admin.metabox>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex h-11 items-center rounded-xl bg-brand-500 px-6 text-sm font-extrabold text-white hover:bg-brand-600">ذخیره لندینگ</button>
            </div>
        </form>
    </x-admin.page>
@endsection
