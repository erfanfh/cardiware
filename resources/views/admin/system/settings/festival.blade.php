@extends('admin.layout')

@section('title', 'تنظیمات صفحه جشنواره')

@section('content')
    <x-admin.page title="تنظیمات صفحه جشنواره"
                  subtitle="لندینگ جشنواره‌های مناسبتی (یلدا، نوروز، تابستان، بلک‌فرایدی و…) — مسیر عمومی: /festival">

        <x-admin.form action="{{ route('admin.settings.festival.store') }}" method="put">
            <x-admin.metabox title="وضعیت و پیام اصلی">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field label="فعال باشد (نمایش صفحه برای کاربران)">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" value="1"
                                   @checked(old('enabled', (bool) get_setting('festival', 'enabled', false)))
                                   class="h-4 w-4 rounded border-slate-300 text-brand-600">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">غیرفعال = صفحه ۴۰۴ می‌شود</span>
                        </label>
                    </x-admin.field>

                    <x-admin.field label="متن بج بالای عنوان">
                        <x-admin.form.input name="badge_text" :value="old('badge_text', get_setting('festival', 'badge_text'))" placeholder="مثلا: جشنواره شب یلدا"/>
                    </x-admin.field>

                    <x-admin.field label="عنوان اصلی">
                        <x-admin.form.input name="title" :value="old('title', get_setting('festival', 'title'))" placeholder="مثلا: یلدای مودینگ‌وی"/>
                    </x-admin.field>

                    <x-admin.field label="زیرعنوان رنگی">
                        <x-admin.form.input name="subtitle" :value="old('subtitle', get_setting('festival', 'subtitle'))" placeholder="مثلا: انفجار تخفیف‌های زمستانی"/>
                    </x-admin.field>

                    <x-admin.field label="رنگ اصلی جشنواره (تم صفحه)"
                                   hint="همه دکمه‌ها/بج‌ها/نوارها با این رنگ رنگ می‌شوند — مثلا #e11d48 برای یلدا، #16a34a برای نوروز.">
                        <x-admin.form.colorpicker name="theme_color" :value="old('theme_color', get_setting('festival', 'theme_color', '#e11d48'))"/>
                    </x-admin.field>

                    <x-admin.field label="متن تخفیف بزرگ">
                        <x-admin.form.input name="discount_text" :value="old('discount_text', get_setting('festival', 'discount_text'))" placeholder="مثلا: ۴۰٪ تخفیف"/>
                    </x-admin.field>
                </div>

                <x-admin.field label="توضیحات">
                    <x-admin.form.textarea name="description" rows="3">{{ old('description', get_setting('festival', 'description')) }}</x-admin.form.textarea>
                </x-admin.field>
            </x-admin.metabox>

            <x-admin.metabox title="شمارش معکوس و کد تخفیف">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field label="شمارش معکوس فعال باشد">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="hidden" name="countdown_enabled" value="0">
                            <input type="checkbox" name="countdown_enabled" value="1"
                                   @checked(old('countdown_enabled', (bool) get_setting('festival', 'countdown_enabled', false)))
                                   class="h-4 w-4 rounded border-slate-300 text-brand-600">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">پایان زمان = مخفی شدن خودکار شمارش</span>
                        </label>
                    </x-admin.field>

                    <x-admin.field label="زمان پایان شمارش" hint="میلادی — مثلا 2026-12-21 00:00:00">
                        <x-admin.form.input name="countdown_ends_at" dir="ltr" :value="old('countdown_ends_at', get_setting('festival', 'countdown_ends_at'))" placeholder="2026-12-21 00:00:00"/>
                    </x-admin.field>

                    <x-admin.field label="کد تخفیف">
                        <x-admin.form.input name="coupon_code" dir="ltr" :value="old('coupon_code', get_setting('festival', 'coupon_code'))" placeholder="YALDA40"/>
                    </x-admin.field>

                    <x-admin.field label="توضیح کد تخفیف">
                        <x-admin.form.input name="coupon_hint" :value="old('coupon_hint', get_setting('festival', 'coupon_hint'))" placeholder="در سبد خرید وارد کنید"/>
                    </x-admin.field>

                    <x-admin.field label="متن دکمه اصلی">
                        <x-admin.form.input name="button_text" :value="old('button_text', get_setting('festival', 'button_text'))" placeholder="مشاهده همه پیشنهادها"/>
                    </x-admin.field>

                    <x-admin.field label="لینک دکمه اصلی">
                        <x-admin.form.input name="button_url" dir="ltr" :value="old('button_url', get_setting('festival', 'button_url'))" placeholder="/products"/>
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="تصاویر و پس‌زمینه">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field label="تصویر بزرگ هدر">
                        <x-admin.form.media-picker mode="field" name="hero_image"
                            :value="['id' => old('hero_image.id', get_setting('festival', 'hero_image')?->id), 'path' => old('hero_image.path', get_setting('festival', 'hero_image')?->path)]"/>
                    </x-admin.field>

                    <x-admin.field label="تصویر پس‌زمینه کل صفحه (اختیاری)">
                        <x-admin.form.media-picker mode="field" name="bg_image"
                            :value="['id' => old('bg_image.id', get_setting('festival', 'bg_image')?->id), 'path' => old('bg_image.path', get_setting('festival', 'bg_image')?->path)]"/>
                    </x-admin.field>

                    <x-admin.field label="پترن پس‌زمینه">
                        <x-admin.form.select name="bg_pattern"
                            :value="old('bg_pattern', get_setting('festival', 'bg_pattern', 'none'))"
                            :options="[
                                ['value' => 'none', 'label' => 'بدون پترن'],
                                ['value' => 'dots', 'label' => 'نقطه‌ای'],
                                ['value' => 'grid', 'label' => 'شبکه‌ای'],
                                ['value' => 'diag', 'label' => 'مورب'],
                            ]"/>
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="پیشنهادهای جشنواره">
                <x-admin.field label="عنوان بخش پیشنهادها">
                    <x-admin.form.input name="deals_title" :value="old('deals_title', get_setting('festival', 'deals_title'))" placeholder="پیشنهادهای جشنواره"/>
                </x-admin.field>

                <x-admin.form.repeater
                    name="deals_items"
                    :items="old('deals_items', get_setting('festival', 'deals_items') ?? [])"
                    title="کارت‌های پیشنهاد"
                    addText="افزودن پیشنهاد"
                    emptyText="هنوز پیشنهادی اضافه نشده است."
                    headingKey="title"
                    headingPlaceholder="پیشنهاد بدون عنوان"
                    :defaultItem="[
                        'title' => '',
                        'short_description' => '',
                        'badge' => '',
                        'price_text' => '',
                        'old_price_text' => '',
                        'link' => '',
                        'button_text' => '',
                        'image_id' => null,
                        'image_path' => null,
                    ]"
                >
                    <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
                        <x-admin.field label="عنوان" class="col-span-2">
                            <x-admin.form.input x-bind:name="field(i, 'title')" x-model="it.title"/>
                        </x-admin.field>

                        <x-admin.field label="توضیح کوتاه" class="col-span-2">
                            <x-admin.form.input x-bind:name="field(i, 'short_description')" x-model="it.short_description"/>
                        </x-admin.field>

                        <x-admin.field label="بج" class="col-span-1">
                            <x-admin.form.input x-bind:name="field(i, 'badge')" x-model="it.badge" placeholder="ویژه"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه" class="col-span-1">
                            <x-admin.form.input x-bind:name="field(i, 'button_text')" x-model="it.button_text" placeholder="مشاهده و خرید"/>
                        </x-admin.field>

                        <x-admin.field label="قیمت" class="col-span-1">
                            <x-admin.form.input x-bind:name="field(i, 'price_text')" x-model="it.price_text" placeholder="۴۹,۰۰۰ تومان"/>
                        </x-admin.field>

                        <x-admin.field label="قیمت قبل" class="col-span-1">
                            <x-admin.form.input x-bind:name="field(i, 'old_price_text')" x-model="it.old_price_text" placeholder="۹۹,۰۰۰ تومان"/>
                        </x-admin.field>

                        <x-admin.field label="لینک" class="col-span-2">
                            <x-admin.form.input x-bind:name="field(i, 'link')" x-model="it.link" dir="ltr"/>
                        </x-admin.field>

                        <x-admin.field label="تصویر" class="lg:col-span-6">
                            <x-admin.form.media-picker mode="repeater" name="image"/>
                        </x-admin.field>
                    </div>
                </x-admin.form.repeater>
            </x-admin.metabox>

            <x-admin.metabox title="یادداشت پایانی">
                <x-admin.field label="متن پایین صفحه (اختیاری)">
                    <x-admin.form.textarea name="footer_note" rows="2">{{ old('footer_note', get_setting('festival', 'footer_note')) }}</x-admin.form.textarea>
                </x-admin.field>
            </x-admin.metabox>

            <x-admin.form.button type="submit" color="brand">ذخیره تنظیمات جشنواره</x-admin.form.button>
        </x-admin.form>
    </x-admin.page>
@endsection
