@props([
    'config' => [],
])

<div
    x-data="seoBox(@js($config))"
    @readability-updated="readabilityIssueCount = $event.detail"
    @title-changed.window="
        entityTitle = $event.detail;
        if (primaryTitleToken) {
            syncVariableValue(primaryTitleToken, $event.detail);
        }
    "
    class="space-y-5"
>
    {{-- Tabs --}}
    <div class="sticky top-3 z-10">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-1.5">
            <div class="flex gap-1.5 overflow-x-auto">
                <template x-for="tab in tabs" :key="tab.key">
                    <button
                        type="button"
                        @click="activeTab = tab.key"
                        class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-2xl px-4 py-2 text-sm font-medium transition-all duration-200"
                        :class="activeTab === tab.key
                            ? 'bg-brand-600 text-white shadow-sm'
                            : 'text-slate-600 hover:bg-white hover:text-slate-900'"
                    >
                        <template x-if="tab.badge !== null">
                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center rounded-full px-1 text-[11px] font-bold"
                                :class="activeTab === tab.key
                                    ? 'bg-orange-100 text-orange-600'
                                    : 'bg-slate-200 text-slate-600'"
                                x-text="tab.badge"
                            ></span>
                        </template>

                        <span class="whitespace-nowrap" x-text="tab.label"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Tab: General --}}
    <section
        x-show="activeTab === 'general'"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="grid grid-cols-12 gap-6">
            <template x-if="showFocusKeyword">
                <div class="col-span-12">
                    <x-admin.field label="کلیدواژه کانونی">
                        <x-admin.form.input
                            name="seo[focus_keyword]"
                            x-model="focusKeyword"
                            x-bind:placeholder="entityTitle"
                        />
                    </x-admin.field>
                </div>
            </template>

            <div class="col-span-12">
                <x-admin.field class="relative flex-1 pt-2" label="عنوان سئو">
                    <x-admin.form.input
                        x-ref="seoTitleInput"
                        name="seo[title]"
                        x-model="seoTitle"
                        x-bind:placeholder="parsedTitleTemplatePlaceholder"
                    />

                    <div class="absolute top-0 left-0 mt-1 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-gray-500">متغیرها: </span>

                        <template x-for="variable in variables" :key="variable.token">
                            <button
                                type="button"
                                @click="insertAtCursor('seoTitleInput', variable.token, 'seoTitle')"
                                class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                :title="variable.description"
                                x-text="variable.label"
                            ></button>
                        </template>
                    </div>
                </x-admin.field>

                <div class="mt-1.5">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="text-slate-500">طول عنوان</span>
                        <span
                            class="font-medium"
                            :class="titleBar.textClass"
                            x-text="resolvedSeoTitleLength + ' کاراکتر'"
                        ></span>
                    </div>

                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="titleBar.barClass"
                            :style="`width: ${titleBar.width}%`"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="col-span-12">
                <x-admin.field class="relative flex-1 pt-2" label="توضیح متا">
                    <x-admin.form.textarea
                        x-ref="seoDescriptionInput"
                        rows="4"
                        name="seo[description]"
                        x-model="seoDescription"
                        x-bind:placeholder="parsedDescriptionTemplatePlaceholder"
                        class="mb-0"
                    />

                    <div class="absolute top-0 left-0 mt-1 flex flex-wrap items-center gap-2">
                        <span class="text-xs text-gray-500">متغیرها: </span>

                        <template x-for="variable in variables" :key="`desc-${variable.token}`">
                            <button
                                type="button"
                                @click="insertAtCursor('seoDescriptionInput', variable.token, 'seoDescription')"
                                class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                :title="variable.description"
                                x-text="variable.label"
                            ></button>
                        </template>
                    </div>
                </x-admin.field>

                <div class="mt-1.5">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="text-slate-500">طول توضیح متا</span>
                        <span
                            class="font-medium"
                            :class="descriptionBar.textClass"
                            x-text="resolvedSeoDescriptionLength + ' کاراکتر'"
                        ></span>
                    </div>

                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="descriptionBar.barClass"
                            :style="`width: ${descriptionBar.width}%`"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="col-span-12">
                <div
                    class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-[0_10px_30px_-20px_rgba(15,23,42,0.35)]">
                    <div class="mb-3 flex items-start gap-3">
                        <div
                            class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100">
                            <img :src="faviconUrl" alt="favicon" class="h-6 w-6">
                        </div>

                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-slate-800">
                                <span x-text="siteTitle || 'نام سایت'"></span>
                            </div>

                            <div class="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                                <span class="direction-ltr truncate" x-text="previewUrl"></span>
                                <span class="inline-block h-1 w-1 rounded-full bg-slate-300"></span>
                                <span>در دسترس</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="line-clamp-2 text-lg font-medium leading-8 text-blue-700"
                        x-text="resolvedSeoTitle || 'عنوان سئو اینجا نمایش داده می‌شود'"
                    ></div>

                    <div class="mt-2 text-sm leading-6 text-slate-600">
                        <span
                            class="line-clamp-3"
                            x-text="resolvedSeoDescription || '---'"
                        ></span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                            عنوان:
                            <span class="ms-1" x-text="resolvedSeoTitleLength"></span>
                        </span>

                        <span
                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                            توضیحات:
                            <span class="ms-1" x-text="resolvedSeoDescriptionLength"></span>
                        </span>

                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium"
                            :class="hasCustomSeoTitle || hasCustomSeoDescription
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-amber-50 text-amber-700'"
                        >
                            <span
                                x-text="hasCustomSeoTitle || hasCustomSeoDescription ? 'سفارشی شده' : 'بر پایه الگوی پیش‌فرض'"
                            ></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tab: Social --}}
    <section
        x-show="activeTab === 'social'"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
            توجه: پر کردن مقادیر زیر اختیاری است. در صورتی که پر نشود، از مقادیر پیش‌فرض همین محتوا استفاده خواهد شد.
        </div>

        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 xl:col-span-6">
                <div class="rounded-2xl border border-dashed border-slate-200 p-4">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-800">Open Graph</h3>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-600">Facebook / LinkedIn / ...</span>
                    </div>

                    <div class="space-y-6">
                        <div class="col-span-12">
                            <x-admin.field class="flex-1" label="عنوان در شبکه‌های اجتماعی">
                                <x-admin.form.input
                                    x-ref="ogTitleInput"
                                    name="seo[og_title]"
                                    x-model="ogTitle"
                                    x-bind:placeholder="parsedOgTitleTemplatePlaceholder"
                                />

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    <template x-for="variable in variables" :key="`og-title-${variable.token}`">
                                        <button
                                            type="button"
                                            @click="insertAtCursor('ogTitleInput', variable.token, 'ogTitle')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            :title="variable.description"
                                            x-text="variable.label"
                                        ></button>
                                    </template>
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field class="flex-1" label="توضیح در شبکه‌های اجتماعی">
                                <x-admin.form.textarea
                                    x-ref="ogDescriptionInput"
                                    rows="4"
                                    name="seo[og_description]"
                                    x-model="ogDescription"
                                    x-bind:placeholder="parsedOgDescriptionTemplatePlaceholder"
                                />

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    <template x-for="variable in variables" :key="`og-desc-${variable.token}`">
                                        <button
                                            type="button"
                                            @click="insertAtCursor('ogDescriptionInput', variable.token, 'ogDescription')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            :title="variable.description"
                                            x-text="variable.label"
                                        ></button>
                                    </template>
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field label="تصویر در شبکه‌های اجتماعی">
                                <x-admin.form.media-picker
                                    mode="field"
                                    name="seo[og_image]"
                                    :value="[
                                        'id' => data_get($config, 'values.ogImageId'),
                                        'path' => data_get($config, 'values.ogImagePath'),
                                    ]"
                                    layout="vertical"
                                />
                            </x-admin.field>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-6">
                <div class="rounded-2xl border border-dashed border-slate-200 p-4">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-800">X</h3>
                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-600">X (Twitter)</span>
                    </div>

                    <div class="space-y-6">
                        <div class="col-span-12">
                            <x-admin.field class="flex-1" label="عنوان در X">
                                <x-admin.form.input
                                    x-ref="xTitleInput"
                                    name="seo[x_title]"
                                    x-model="xTitle"
                                />

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    <template x-for="variable in variables" :key="`x-title-${variable.token}`">
                                        <button
                                            type="button"
                                            @click="insertAtCursor('xTitleInput', variable.token, 'xTitle')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            :title="variable.description"
                                            x-text="variable.label"
                                        ></button>
                                    </template>
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field class="flex-1" label="توضیح در X">
                                <x-admin.form.textarea
                                    x-ref="xDescriptionInput"
                                    rows="4"
                                    name="seo[x_description]"
                                    x-model="xDescription"
                                />

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    <template x-for="variable in variables" :key="`x-desc-${variable.token}`">
                                        <button
                                            type="button"
                                            @click="insertAtCursor('xDescriptionInput', variable.token, 'xDescription')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            :title="variable.description"
                                            x-text="variable.label"
                                        ></button>
                                    </template>
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field label="تصویر در X">
                                <x-admin.form.media-picker
                                    mode="field"
                                    name="seo[x_image]"
                                    :value="[
                                        'id' => data_get($config, 'values.xImageId'),
                                        'path' => data_get($config, 'values.xImagePath'),
                                    ]"
                                    layout="vertical"
                                />
                            </x-admin.field>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tab: Advanced --}}
    <section
        x-show="activeTab === 'advanced'"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 xl:col-span-6">
                <div class="rounded-2xl border border-dashed border-slate-200 p-4">
                    <h3 class="mb-4 text-sm font-semibold text-slate-800">Canonical</h3>

                    <x-admin.field label="نشانی قانونی">
                        <x-admin.form.input
                            name="seo[canonical_url]"
                            x-model="canonicalUrl"
                            class="direction-ltr"
                        />
                        <p class="mt-2 text-xs text-slate-500">
                            اگر خالی بماند، نشانی پیش‌فرض همین محتوا استفاده می‌شود.
                        </p>
                    </x-admin.field>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-6">
                <div class="rounded-2xl border border-dashed border-slate-200 p-4">
                    <h3 class="mb-4 text-sm font-semibold text-slate-800">Meta Robots</h3>

                    <x-admin.field label="مقدار robots">
                        <x-admin.form.input
                            name="seo[robots]"
                            x-model="robots"
                            class="direction-ltr"
                        />
                        <p class="mt-2 text-xs text-slate-500">
                            مثال: index, follow یا noindex, nofollow
                        </p>
                    </x-admin.field>
                </div>
            </div>
        </div>
    </section>

    {{-- Tab: Analysis --}}
    <section
        x-show="activeTab === 'analysis' && analysisEnabled"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
            توجه: آنالیز سئو بعد از ذخیره‌سازی تغییرات، به‌روز می‌شود.
        </div>

        <div class="mt-5 grid grid-cols-12 gap-5">
            <div class="col-span-12">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">امتیاز سئو</div>
                            <div class="mt-1 text-xs text-slate-500">
                                خلاصه وضعیت سئوی این محتوا بر اساس تحلیل فعلی
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-full text-lg font-black"
                                :class="analysisScoreClass"
                                x-text="analysis.score"
                            ></div>

                            <div class="text-sm">
                                <div class="font-semibold text-slate-800">از 100</div>
                                <div
                                    class="mt-1 font-medium"
                                    :class="analysisScoreTextClass"
                                    x-text="analysisScoreLabel"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="rounded-xl bg-white px-4 py-3">
                            <div class="text-xs text-slate-500">مشکلات</div>
                            <div class="mt-1 text-base font-bold text-red-600" x-text="analysis.badges.problems"></div>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                            <div class="text-xs text-slate-500">نیاز به بهبود</div>
                            <div class="mt-1 text-base font-bold text-amber-600"
                                 x-text="analysis.badges.improvements"></div>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3">
                            <div class="text-xs text-slate-500">موارد خوب</div>
                            <div class="mt-1 text-base font-bold text-emerald-600" x-text="analysis.badges.good"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12">
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-red-700">مشکلات</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-red-700"
                              x-text="analysis.badges.problems"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in analysis.problems" :key="item.key ?? item.title">
                            <div class="rounded-xl bg-white p-3 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>

                                    <template x-if="item.key">
                                        <span
                                            class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500"
                                            x-text="item.key"></span>
                                    </template>
                                </div>

                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>

                        <template x-if="analysis.problems.length === 0">
                            <div class="rounded-xl bg-white p-3 text-sm text-slate-500">
                                در حال حاضر مشکلی برای این محتوا ثبت نشده است.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-span-12">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-amber-700">نیاز به بهبود</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700"
                              x-text="analysis.badges.improvements"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in analysis.improvements" :key="item.key ?? item.title">
                            <div class="rounded-xl bg-white p-3 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>

                                    <template x-if="item.key">
                                        <span
                                            class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500"
                                            x-text="item.key"></span>
                                    </template>
                                </div>

                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>

                        <template x-if="analysis.improvements.length === 0">
                            <div class="rounded-xl bg-white p-3 text-sm text-slate-500">
                                موردی برای بهبود ثبت نشده است.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-span-12">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-emerald-700">موارد خوب</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700"
                              x-text="analysis.badges.good"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in analysis.good" :key="item.key ?? item.title">
                            <div class="rounded-xl bg-white p-3 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>

                                    <template x-if="item.key">
                                        <span
                                            class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500"
                                            x-text="item.key"></span>
                                    </template>
                                </div>

                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>

                        <template x-if="analysis.good.length === 0">
                            <div class="rounded-xl bg-white p-3 text-sm text-slate-500">
                                هنوز نتیجه مثبتی برای نمایش ثبت نشده است.
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tab: Readability --}}
    <section
        x-data="seoReadability(@js($config['readabilityField'] ?? 'body'))"
        x-show="activeTab === 'readability' && readabilityEnabled"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">خوانایی متن اصلی</h3>
                <p class="mt-1 text-xs leading-6 text-slate-500">با ویرایش متن، نتیجه خودکار به‌روز می‌شود؛ نیازی به ذخیره نیست.</p>
            </div>
            <span class="rounded-full border px-3 py-1 text-xs font-semibold" role="status"
                  :class="readabilityColor(readability.status)"
                  x-text="readability.state === 'short' ? 'متن کوتاه' : readabilityLabel(readability.status)"></span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <template x-for="stat in [
                {label: 'کلمه', value: readability.words},
                {label: 'جمله', value: readability.sentences},
                {label: 'پاراگراف', value: readability.paragraphs},
                {label: 'دقیقه مطالعهٔ تقریبی', value: readability.readingMinutes}
            ]" :key="stat.label">
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-lg font-bold text-slate-800" x-text="stat.value.toLocaleString('fa-IR')"></div>
                    <div class="mt-1 text-xs text-slate-500" x-text="stat.label"></div>
                </div>
            </template>
        </div>

        <p x-show="readability.state !== 'ready'" class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-600"
           x-text="readability.state === 'empty'
               ? 'برای شروع تحلیل، متن اصلی محتوا را وارد کنید. تصویر، ویدیو و کد به‌تنهایی متن قابل تحلیل محسوب نمی‌شوند.'
               : 'برای ارزیابی قابل اتکاتر، دست‌کم ۵۰ کلمه لازم است. کوتاه بودن توضیحات به‌خودی‌خود ایراد نیست؛ آمار متن فعلی در بالا نمایش داده می‌شود.'"></p>

        <div class="mt-4 space-y-3">
            <template x-for="check in readability.checks" :key="check.key">
                <div class="rounded-xl border p-4" :class="readabilityColor(check.status)">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm font-semibold">
                        <span x-text="check.title"></span>
                        <span class="text-xs" x-text="readabilityLabel(check.status)"></span>
                    </div>
                    <p class="mt-2 text-xs leading-6" x-text="check.description"></p>
                    <template x-if="check.examples.length">
                        <details class="mt-3 text-xs">
                            <summary class="cursor-pointer font-medium">نمونه‌های قابل اصلاح از متن</summary>
                            <ul class="mt-2 space-y-2">
                                <template x-for="(example, index) in check.examples" :key="index">
                                    <li class="break-words rounded-lg bg-white p-3 leading-6 text-slate-600" x-text="example"></li>
                                </template>
                            </ul>
                        </details>
                    </template>
                </div>
            </template>
        </div>
        <p class="mt-4 text-xs leading-6 text-slate-500">این معیارها راهنمای نگارش‌اند و سختی واژگان یا کیفیت معنایی متن را نمی‌سنجند. زمان مطالعه بر پایهٔ حدود ۲۰۰ کلمه در دقیقه است؛ عنوان، خلاصه و توضیح متا در این تحلیل محاسبه نمی‌شوند.</p>
    </section>
</div>

@push('scripts')
    <script>
        function seoBox(config) {
            return {
                activeTab: config.defaultTab ?? 'general',

                primaryTitleToken: config.primaryTitleToken ?? null,
                variables: config.variables ?? [],
                templates: config.templates ?? {},

                entityType: config.entityType ?? 'content',
                entityTitle: config.values?.entityTitle ?? '',
                slug: config.values?.slug ?? '',

                siteTitle: config.values?.siteTitle ?? '',
                separator: config.values?.separator ?? '|',
                variableValues: config.values?.variableValues ?? {},

                focusKeyword: config.values?.focusKeyword ?? '',

                seoTitle: config.values?.seoTitle ?? '',
                seoDescription: config.values?.seoDescription ?? '',

                canonicalUrl: config.values?.canonicalUrl ?? '',
                robots: config.values?.robots ?? '',

                ogTitle: config.values?.ogTitle ?? '',
                ogDescription: config.values?.ogDescription ?? '',
                ogImageId: config.values?.ogImageId ?? null,
                ogImagePath: config.values?.ogImagePath ?? '',

                xTitle: config.values?.xTitle ?? '',
                xDescription: config.values?.xDescription ?? '',
                xImageId: config.values?.xImageId ?? null,
                xImagePath: config.values?.xImagePath ?? '',

                analysisEnabled: config.analysisEnabled ?? false,
                readabilityEnabled: config.readabilityEnabled ?? false,
                readabilityIssueCount: null,
                showFocusKeyword: config.showFocusKeyword ?? true,

                faviconUrl: config.faviconUrl ?? '/favicon/favicon-32x32.webp',
                previewBaseUrl: config.previewBaseUrl ?? window.location.origin,
                previewFallbackPath: config.previewFallbackPath ?? '/sample-slug',

                analysis: config.analysis ?? {
                    score: 0,
                    badges: {
                        problems: 0,
                        improvements: 0,
                        good: 0,
                    },
                    problems: [],
                    improvements: [],
                    good: [],
                },

                get tabs() {
                    const tabs = [
                        {key: 'general', label: 'عمومی', badge: null},
                        {key: 'social', label: 'اجتماعی', badge: null},
                        {key: 'advanced', label: 'پیشرفته', badge: null},
                    ];

                    if (this.analysisEnabled) {
                        tabs.push({
                            key: 'analysis',
                            label: 'تجزیه و تحلیل سئو',
                            badge: (this.analysis?.badges?.problems ?? 0)
                                + (this.analysis?.badges?.improvements ?? 0)
                                + (this.analysis?.badges?.good ?? 0),
                        });
                    }

                    if (this.readabilityEnabled) {
                        tabs.push({
                            key: 'readability',
                            label: 'آنالیز خوانایی',
                            badge: this.readabilityIssueCount,
                        });
                    }

                    return tabs;
                },

                syncVariableValue(token, value) {
                    if (!token) {
                        return;
                    }

                    this.variableValues[token] = value ?? '';
                },

                parseTemplate(template) {
                    if (!template) {
                        return '';
                    }

                    let parsed = template;

                    Object.entries(this.variableValues).forEach(([token, value]) => {
                        parsed = parsed.split(token).join((value ?? '').toString().trim());
                    });

                    return parsed.replace(/\s+/g, ' ').trim();
                },

                get parsedTitleTemplatePlaceholder() {
                    return this.parseTemplate(this.templates.title);
                },

                get parsedDescriptionTemplatePlaceholder() {
                    return this.parseTemplate(this.templates.description);
                },

                get parsedOgTitleTemplatePlaceholder() {
                    return this.parseTemplate(this.templates.ogTitle || this.templates.title);
                },

                get parsedOgDescriptionTemplatePlaceholder() {
                    return this.parseTemplate(this.templates.ogDescription || this.templates.description);
                },

                get resolvedSeoTitle() {
                    return this.seoTitle?.trim()
                        ? this.parseTemplate(this.seoTitle)
                        : this.parseTemplate(this.templates.title);
                },

                get resolvedSeoDescription() {
                    return this.seoDescription?.trim()
                        ? this.parseTemplate(this.seoDescription)
                        : this.parseTemplate(this.templates.description);
                },

                get resolvedOgTitle() {
                    return this.ogTitle?.trim()
                        ? this.parseTemplate(this.ogTitle)
                        : this.parseTemplate(this.templates.ogTitle || this.templates.title);
                },

                get resolvedOgDescription() {
                    if (this.ogDescription?.trim()) {
                        return this.parseTemplate(this.ogDescription);
                    }

                    if (this.seoDescription?.trim()) {
                        return this.parseTemplate(this.seoDescription);
                    }

                    return this.parseTemplate(this.templates.ogDescription || this.templates.description);
                },

                get resolvedSeoTitleLength() {
                    return this.resolvedSeoTitle.length;
                },

                get resolvedSeoDescriptionLength() {
                    return this.resolvedSeoDescription.length;
                },

                get hasCustomSeoTitle() {
                    return !!this.seoTitle?.trim();
                },

                get hasCustomSeoDescription() {
                    return !!this.seoDescription?.trim();
                },

                insertAtCursor(refName, token, modelName) {
                    const input = this.$refs[refName];

                    if (!input) {
                        if (Object.prototype.hasOwnProperty.call(this, modelName)) {
                            const currentValue = this[modelName] ?? '';
                            this[modelName] = currentValue ? `${currentValue} ${token}` : token;
                        }

                        return;
                    }

                    const start = input.selectionStart ?? input.value.length;
                    const end = input.selectionEnd ?? input.value.length;
                    const currentValue = this[modelName] ?? '';

                    this[modelName] = currentValue.slice(0, start) + token + currentValue.slice(end);

                    this.$nextTick(() => {
                        input.focus();

                        const newCursorPosition = start + token.length;

                        if (typeof input.setSelectionRange === 'function') {
                            input.setSelectionRange(newCursorPosition, newCursorPosition);
                        }
                    });
                },

                progressMeta(length, goodMin, goodMax, max) {
                    const width = Math.min((length / max) * 100, 100);

                    if (length === 0) {
                        return {
                            width: 0,
                            barClass: 'bg-slate-300',
                            textClass: 'text-slate-500',
                        };
                    }

                    if (length >= goodMin && length <= goodMax) {
                        return {
                            width,
                            barClass: 'bg-emerald-500',
                            textClass: 'text-emerald-600',
                        };
                    }

                    if ((length >= goodMin - 10 && length < goodMin) || (length > goodMax && length <= max)) {
                        return {
                            width,
                            barClass: 'bg-amber-500',
                            textClass: 'text-amber-600',
                        };
                    }

                    return {
                        width,
                        barClass: 'bg-red-500',
                        textClass: 'text-red-600',
                    };
                },

                get titleBar() {
                    return this.progressMeta(this.resolvedSeoTitleLength, 45, 60, 70);
                },

                get descriptionBar() {
                    return this.progressMeta(this.resolvedSeoDescriptionLength, 120, 160, 180);
                },

                get previewUrl() {
                    const cleanedSlug = (this.slug || '').replace(/^\/+|\/+$/g, '');
                    const base = (this.previewBaseUrl || '').replace(/\/+$/g, '');
                    const fallbackPath = (this.previewFallbackPath || '/sample-slug').replace(/^\/?/g, '/');

                    if (!cleanedSlug) {
                        return `${base}${fallbackPath}`;
                    }

                    return `${base}/${cleanedSlug}`;
                },

                get analysisScoreLabel() {
                    if ((this.analysis?.score ?? 0) >= 80) {
                        return 'وضعیت خوب';
                    }

                    if ((this.analysis?.score ?? 0) >= 50) {
                        return 'نیاز به بهبود';
                    }

                    return 'دارای مشکل';
                },

                get analysisScoreClass() {
                    if ((this.analysis?.score ?? 0) >= 80) {
                        return 'bg-emerald-100 text-emerald-700';
                    }

                    if ((this.analysis?.score ?? 0) >= 50) {
                        return 'bg-amber-100 text-amber-700';
                    }

                    return 'bg-red-100 text-red-700';
                },

                get analysisScoreTextClass() {
                    if ((this.analysis?.score ?? 0) >= 80) {
                        return 'text-emerald-700';
                    }

                    if ((this.analysis?.score ?? 0) >= 50) {
                        return 'text-amber-700';
                    }

                    return 'text-red-700';
                },
            };
        }
    </script>
@endpush
