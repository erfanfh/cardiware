@php
    use App\Domain\Seo\Enums\SeoTemplateVariableEnum;

    $postSeoVariables = collect(SeoTemplateVariableEnum::cases())
        ->filter(fn (SeoTemplateVariableEnum $variable) => $variable->isAvailableFor('post'))
        ->map(fn (SeoTemplateVariableEnum $variable) => [
            'token' => $variable->token(),
            'label' => $variable->label(),
            'description' => $variable->description(),
        ])
        ->values()
        ->all();

    $postSeoTitleTemplate = get_setting('seo', 'post_seo_title');
    $postSeoDescriptionTemplate = get_setting('seo', 'post_seo_description');
    $postSocialSeoTitleTemplate = get_setting('seo', 'post_social_seo_title');
    $postSocialSeoDescriptionTemplate = get_setting('seo', 'post_social_seo_description');

    $siteTitle = app(\App\Application\Seo\Actions\GetSeoDefaultsAction::class)->execute()['site_name'];
@endphp

<div
    x-data="postSeoBox({
        templates: {
            title: @js($postSeoTitleTemplate),
            description: @js($postSeoDescriptionTemplate),
            ogTitle: @js($postSocialSeoTitleTemplate),
            ogDescription: @js($postSocialSeoDescriptionTemplate),
        },

        values: {
            postTitle: @js(old('title', $post->title ?? '')),
            slug: @js(old('slug', $post->slug ?? '')),
            siteTitle: @js($siteTitle),
            separator: @js(get_setting('seo', 'separator')),

            focusKeyword: @js(old('seo.focus_keyword', $post->seoMeta?->focus_keyword ?? '')),

            seoTitle: @js(old('seo.title', $post->seoMeta?->title ?? '')),
            seoDescription: @js(old('seo.description', $post->seoMeta?->description ?? '')),

            canonicalUrl: @js(old('seo.canonical_url', $post->seoMeta?->canonical_url ?? '')),
            robots: @js(old('seo.robots', $post->seoMeta?->robots ?? '')),

            ogTitle: @js(old('seo.og_title', $post->seoMeta?->og_title ?? '')),
            ogDescription: @js(old('seo.og_description', $post->seoMeta?->og_description ?? '')),
            ogImageId: @js(old('seo.og_image.id', $post->seoMeta?->og_image_id ?? null)),
            ogImagePath: @js(old('seo.og_image.path', $post->seoMeta?->ogImage?->url ?? '')),

            xTitle: @js(old('seo.x_title', $post->seoMeta?->x_title ?? '')),
            xDescription: @js(old('seo.x_description', $post->seoMeta?->x_description ?? '')),
            xImageId: @js(old('seo.x_image.id', $post->seoMeta?->x_image_id ?? null)),
            xImagePath: @js(old('seo.x_image.path', $post->seoMeta?->xImage?->url ?? '')),
        }
    })"
    @title-changed.window="postTitle = $event.detail"
    class="space-y-5"
>
    {{-- Summary Strip --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">عنوان سئو</div>
            <div class="mt-1 flex items-center gap-2">
                    <span
                        class="inline-flex h-2.5 w-2.5 rounded-full"
                        :class="hasCustomSeoTitle ? 'bg-emerald-500' : 'bg-amber-500'"
                    ></span>
                <div class="text-sm font-medium text-slate-800"
                     x-text="hasCustomSeoTitle ? 'اختصاصی' : 'از الگو'"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">توضیح متا</div>
            <div class="mt-1 flex items-center gap-2">
                    <span
                        class="inline-flex h-2.5 w-2.5 rounded-full"
                        :class="hasCustomSeoDescription ? 'bg-emerald-500' : 'bg-amber-500'"
                    ></span>
                <div class="text-sm font-medium text-slate-800"
                     x-text="hasCustomSeoDescription ? 'اختصاصی' : 'از الگو'"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">شبکه‌های اجتماعی</div>
            <div class="mt-1 flex items-center gap-2">
                    <span
                        class="inline-flex h-2.5 w-2.5 rounded-full"
                        :class="socialStatusColor"
                    ></span>
                <div class="text-sm font-medium text-slate-800" x-text="socialStatusLabel"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">Canonical</div>
            <div class="mt-1 text-sm font-medium text-slate-800"
                 x-text="canonicalUrl.trim() ? 'سفارشی' : 'پیش‌فرض'"></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">Robots</div>
            <div class="mt-1 text-sm font-medium text-slate-800" x-text="robots.trim() || 'پیش‌فرض'"></div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="sticky top-3 z-10">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-1.5">
            <div class="flex gap-1.5 overflow-x-auto">
                <template x-for="tab in tabs" :key="tab.key">
                    <button
                        type="button"
                        @click="activeTab = tab.key"
                        class="cursor-pointer inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2 text-sm font-medium transition-all duration-200"
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
            <div class="col-span-12">
                <x-admin.field label="کلیدواژه کانونی">
                    <x-admin.form.input
                        name="seo[focus_keyword]"
                        x-model="focusKeyword"
                        x-bind:placeholder="postTitle"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12">
                <x-admin.field class="flex-1 pt-2 relative" label="عنوان سئو">
                    <x-admin.form.input
                        x-ref="seoTitleInput"
                        name="seo[title]"
                        x-model="seoTitle"
                        x-bind:placeholder="parsedTitleTemplatePlaceholder"
                    />

                    <div class="mt-1 flex flex-wrap gap-2 items-center absolute top-0 left-0">
                        <span class="text-xs text-gray-500">متغیرها: </span>
                        @foreach($postSeoVariables as $variable)
                            <button
                                type="button"
                                @click="insertAtCursor('seoTitleInput', '{{ $variable['token'] }}', 'seoTitle')"
                                class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                title="{{ $variable['description'] }}"
                            >
                                {{ $variable['label'] }}
                            </button>
                        @endforeach
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
                <x-admin.field class="flex-1 relative pt-2" label="توضیح متا">
                    <x-admin.form.textarea
                        x-ref="seoDescriptionInput"
                        rows="4"
                        name="seo[description]"
                        x-model="seoDescription"
                        x-bind:placeholder="parsedDescriptionTemplatePlaceholder"
                        class="mb-0"
                    />

                    <div class="mt-1 flex flex-wrap gap-2 items-center absolute top-0 left-0">
                        <span class="text-xs text-gray-500">متغیرها: </span>
                        @foreach($postSeoVariables as $variable)
                            <button
                                type="button"
                                @click="insertAtCursor('seoDescriptionInput', '{{ $variable['token'] }}', 'seoTitle')"
                                class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                title="{{ $variable['description'] }}"
                            >
                                {{ $variable['label'] }}
                            </button>
                        @endforeach
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
                            <img src="{{ asset('favicon/favicon-32x32.webp') }}" alt="favicon" class="w-6 h-6">
                        </div>

                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-slate-800">
                                <span x-text="siteTitle || 'نام سایت'"></span>
                            </div>

                            <div class="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                                <span class="truncate direction-ltr" x-text="previewUrl"></span>
                                <span class="bg-slate-300 w-1 h-1 rounded-full inline-block"></span>
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
                            x-text="hasCustomSeoTitle || hasCustomSeoDescription ? 'سفارشی شده' : 'بر پایه الگوی پیش‌فرض'"></span>
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
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700 mb-5">
            توجه: پر کردن مقادیر زیر اختیاری است. در صورتی که پر نشود، از مقادیر کنونی نوشته استفاده خواهد
            شد.
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
                            <x-admin.field class="flex-1 relative" label="عنوان در شبکه های اجتماعی">
                                <x-admin.form.input
                                    x-ref="ogTitleInput"
                                    name="seo[og_title]"
                                    x-model="ogTitle"
                                    x-bind:placeholder="parsedOgTitleTemplatePlaceholder"
                                />

                                <div class="mt-1 flex flex-wrap gap-2 items-center">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    @foreach($postSeoVariables as $variable)
                                        <button
                                            type="button"
                                            @click="insertAtCursor('ogTitleInput', '{{ $variable['token'] }}', 'ogTitle')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            title="{{ $variable['description'] }}"
                                        >
                                            {{ $variable['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field class="flex-1 relative" label="توضیح در شبکه های اجتماعی">
                                <x-admin.form.textarea
                                    x-ref="ogDescriptionInput"
                                    rows="4"
                                    name="seo[og_description]"
                                    x-model="ogDescription"
                                    x-bind:placeholder="parsedOgDescriptionTemplatePlaceholder"
                                />

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    @foreach($postSeoVariables as $variable)
                                        <button
                                            type="button"
                                            @click="insertAtCursor('ogDescriptionInput', '{{ $variable['token'] }}', 'ogDescription')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            title="{{ $variable['description'] }}"
                                        >
                                            {{ $variable['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field label="تصویر در شبکه های اجتماعی">
                                <x-admin.form.media-picker
                                    mode="field"
                                    name="seo[og_image]"
                                    :value="[
                                        'id' => old('seo.og_image.id', $post?->seoMeta?->og_image_id),
                                        'path' => old('seo.og_image.path', $post?->seoMeta?->ogImage?->url),
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
                            <x-admin.field class="flex-1 relative" label="عنوان در X">
                                <x-admin.form.input
                                    x-ref="xTitleInput"
                                    name="seo[x_title]"
                                    x-model="xTitle"
                                />

                                <div class="mt-1 flex flex-wrap gap-2 items-center">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    @foreach($postSeoVariables as $variable)
                                        <button
                                            type="button"
                                            @click="insertAtCursor('xTitleInput', '{{ $variable['token'] }}', 'xTitle')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            title="{{ $variable['description'] }}"
                                        >
                                            {{ $variable['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field class="flex-1 relative" label="توضیح در X">
                                <x-admin.form.textarea
                                    x-ref="xDescriptionInput"
                                    rows="4"
                                    name="seo[x_description]"
                                    x-model="xDescription"
                                />

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500">متغیرها: </span>

                                    @foreach($postSeoVariables as $variable)
                                        <button
                                            type="button"
                                            @click="insertAtCursor('xDescriptionInput', '{{ $variable['token'] }}', 'xDescription')"
                                            class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-100 px-2 py-0.5 text-[0.7rem] text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                            title="{{ $variable['description'] }}"
                                        >
                                            {{ $variable['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </x-admin.field>
                        </div>

                        <div class="col-span-12">
                            <x-admin.field label="تصویر در X">
                                <x-admin.form.media-picker
                                    mode="field"
                                    name="seo[x_image]"
                                    :value="[
                                        'id' => old('seo.x_image.id', $post?->seoMeta?->x_image_id),
                                        'path' => old('seo.x_image.path', $post?->seoMeta?->xImage?->url),
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
                            اگر خالی بماند، نشانی پیش‌فرض همین نوشته استفاده می‌شود.
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

    {{-- Tab: SEO Analysis --}}
    <section
        x-show="activeTab === 'analysis'"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 xl:col-span-4">
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-red-700">مشکلات</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-red-700"
                              x-text="analysis.badges.problems"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in analysis.problems" :key="item.title">
                            <div class="rounded-xl bg-white p-3">
                                <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>
                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-4">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-amber-700">نیاز به بهبود</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700"
                              x-text="analysis.badges.improvements"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in analysis.improvements" :key="item.title">
                            <div class="rounded-xl bg-white p-3">
                                <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>
                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-4">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-emerald-700">موارد خوب</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700"
                              x-text="analysis.badges.good"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in analysis.good" :key="item.title">
                            <div class="rounded-xl bg-white p-3">
                                <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>
                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tab: Readability --}}
    <section
        x-show="activeTab === 'readability'"
        x-transition.opacity.duration.200ms
        class="rounded-2xl border border-slate-200 bg-white p-5"
    >
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 xl:col-span-4">
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-red-700">مشکلات خوانایی</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-red-700"
                              x-text="readability.badges.problems"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in readability.problems" :key="item.title">
                            <div class="rounded-xl bg-white p-3">
                                <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>
                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-4">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-amber-700">پیشنهادها</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700"
                              x-text="readability.badges.improvements"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in readability.improvements" :key="item.title">
                            <div class="rounded-xl bg-white p-3">
                                <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>
                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-4">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-emerald-700">موارد خوب</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700"
                              x-text="readability.badges.good"></span>
                    </div>

                    <div class="space-y-3">
                        <template x-for="item in readability.good" :key="item.title">
                            <div class="rounded-xl bg-white p-3">
                                <div class="text-sm font-semibold text-slate-800" x-text="item.title"></div>
                                <div class="mt-1 text-xs leading-5 text-slate-600" x-text="item.description"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
    <script>
        function postSeoBox(config) {
            return {
                activeTab: 'general',
                variableMenuTarget: null,

                tabs: [
                    {key: 'general', label: 'عمومی', badge: null},
                    {key: 'social', label: 'اجتماعی', badge: null},
                    {key: 'advanced', label: 'پیشرفته', badge: null},
                    {key: 'analysis', label: 'تجزیه و تحلیل سئو', badge: 6},
                    {key: 'readability', label: 'آنالیز خوانایی', badge: 5},
                ],

                variables: config.variables ?? [],
                templates: config.templates ?? {},

                postTitle: config.values?.postTitle ?? '',
                slug: config.values?.slug ?? '',
                siteTitle: config.values?.siteTitle ?? '',
                separator: config.values?.separator ?? '|',

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

                analysis: {
                    badges: {
                        problems: 2,
                        improvements: 2,
                        good: 2,
                    },
                    problems: [
                        {
                            title: 'کلیدواژه کانونی در عنوان دیده نمی‌شود',
                            description: 'بهتر است کلیدواژه کانونی در عنوان سئو حضور داشته باشد تا ارتباط صفحه واضح‌تر شود.',
                        },
                        {
                            title: 'تصویر اجتماعی تنظیم نشده است',
                            description: 'برای نمایش بهتر لینک در شبکه‌های اجتماعی، یک تصویر اختصاصی انتخاب کنید.',
                        },
                    ],
                    improvements: [
                        {
                            title: 'طول توضیح متا می‌تواند بهتر شود',
                            description: 'توضیح متا کمی کوتاه به نظر می‌رسد و می‌تواند کامل‌تر و جذاب‌تر باشد.',
                        },
                        {
                            title: 'Canonical هنوز سفارشی نشده است',
                            description: 'اگر این صفحه نسخه خاصی دارد، بررسی کنید که canonical درست تنظیم شده باشد.',
                        },
                    ],
                    good: [
                        {
                            title: 'عنوان صفحه موجود است',
                            description: 'برای این نوشته عنوان سئو یا الگوی پیش‌فرض در دسترس است.',
                        },
                        {
                            title: 'ساختار تب‌بندی و داده‌ها آماده است',
                            description: 'بخش سئو برای توسعه تحلیل‌های حرفه‌ای در گام‌های بعدی آماده شده است.',
                        },
                    ],
                },

                readability: {
                    badges: {
                        problems: 2,
                        improvements: 2,
                        good: 1,
                    },
                    problems: [
                        {
                            title: 'بعضی جمله‌ها طولانی هستند',
                            description: 'بهتر است جمله‌های خیلی طولانی به چند جمله کوتاه‌تر تقسیم شوند.',
                        },
                        {
                            title: 'پاراگراف‌های بلند دیده می‌شود',
                            description: 'پاراگراف‌های کوتاه‌تر خوانایی متن را بهتر می‌کنند.',
                        },
                    ],
                    improvements: [
                        {
                            title: 'استفاده از تیترهای فرعی می‌تواند بیشتر شود',
                            description: 'افزودن تیترهای فرعی باعث اسکن‌پذیری بهتر محتوا می‌شود.',
                        },
                        {
                            title: 'تنوع واژگان قابل بهبود است',
                            description: 'در بعضی بخش‌ها تکرار واژه‌ها زیاد است و می‌توان متن را روان‌تر کرد.',
                        },
                    ],
                    good: [
                        {
                            title: 'ساختار کلی محتوا قابل توسعه است',
                            description: 'محتوا ظرفیت خوبی برای بهبود خوانایی و تحلیل خودکار دارد.',
                        },
                    ],
                },

                parseTemplate(template) {
                    if (!template) {
                        return '';
                    }

                    const replacements = {
                        '{site_title}': this.siteTitle?.trim() || '',
                        '{separator}': this.separator?.trim() || '',
                        '{post_title}': this.postTitle?.trim() || '',
                    };

                    let parsed = template;

                    Object.entries(replacements).forEach(([token, value]) => {
                        parsed = parsed.split(token).join(value);
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
                    return this.parseTemplate(this.templates.ogTitle);
                },

                get parsedOgDescriptionTemplatePlaceholder() {
                    return this.parseTemplate(this.templates.ogDescription);
                },

                get resolvedSeoTitle() {
                    return this.seoTitle?.trim()
                        ? this.parseTemplate(this.seoTitle)
                        : this.parseTemplate(this.templates.title);
                },

                get resolvedOgTitle() {
                    return this.ogTitle?.trim()
                        ? this.parseTemplate(this.ogTitle)
                        : this.parseTemplate(this.templates.ogTitle);
                },

                get resolvedOgDescription() {
                    if (this.ogDescription?.trim()) {
                        return this.parseTemplate(this.ogDescription);
                    }

                    if (this.seoDescription?.trim()) {
                        return this.parseTemplate(this.seoDescription);
                    }

                    return this.parseTemplate(this.templates.ogDescription);
                },

                get resolvedSeoTitleLength() {
                    return this.resolvedSeoTitle.length;
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

                    const newValue = currentValue.slice(0, start) + token + currentValue.slice(end);

                    this[modelName] = newValue;

                    this.$nextTick(() => {
                        input.focus();

                        const newCursorPosition = start + token.length;

                        if (typeof input.setSelectionRange === 'function') {
                            input.setSelectionRange(newCursorPosition, newCursorPosition);
                        }
                    });
                },

                get resolvedSeoDescription() {
                    return this.seoDescription?.trim()
                        ? this.parseTemplate(this.seoDescription)
                        : this.parseTemplate(this.templates.description);
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

                progressMeta(length, goodMin, goodMax, max) {
                    let width = Math.min((length / max) * 100, 100);

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
                    return cleanedSlug ? `https://moddingway.ir/${cleanedSlug}` : 'https://moddingway.ir/post-slug';
                },

                get socialStatusLabel() {
                    const hasOg = !!(this.ogTitle?.trim() || this.ogDescription?.trim() || this.ogImagePath?.trim());
                    const hasX = !!(this.xTitle?.trim() || this.xDescription?.trim() || this.xImagePath?.trim());

                    if (hasOg && hasX) {
                        return 'کامل';
                    }

                    if (hasOg || hasX) {
                        return 'نیمه‌کامل';
                    }

                    return 'تنظیم نشده';
                },

                get socialStatusColor() {
                    if (this.socialStatusLabel === 'کامل') {
                        return 'bg-emerald-500';
                    }

                    if (this.socialStatusLabel === 'نیمه‌کامل') {
                        return 'bg-amber-500';
                    }

                    return 'bg-slate-400';
                },
            };
        }
    </script>
@endpush
