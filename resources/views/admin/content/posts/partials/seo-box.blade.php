<div
    x-data="postSeoBox({
        title: @js(old('title', $post->title ?? '')),
        seoTitle: @js(old('seo.title', $post->seoMeta?->title ?? '')),
        seoDescription: @js(old('seo.description', $post->seoMeta?->description ?? '')),
        titleTemplate: @js(get_setting('seo', 'post_seo_title')),
        descriptionTemplate: @js(get_setting('seo', 'post_seo_description')),
        siteTitle: @js(get_setting('seo', 'site_name')),
        separator: @js(get_setting('seo', 'separator')),
        variables: @js(collect($postSeoVariables ?? [])->map(fn ($item) => [
            'key' => $item['key'],
            'token' => $item['token'],
            'label' => $item['label'],
            'description' => $item['description'] ?? null,
        ])->values()->all()),
    })"
    class="space-y-5"
>
    <div class="flex items-center gap-2">
        <button
            type="button"
            @click="activeTab = 'general'"
            :class="activeTab === 'general'
            ? 'bg-orange-500 text-white shadow-sm'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="cursor-pointer px-4 py-2 text-sm font-medium rounded-2xl transition-all duration-200"
        >
            عمومی
        </button>

        <button
            type="button"
            @click="activeTab = 'advanced'"
            :class="activeTab === 'advanced'
            ? 'bg-orange-500 text-white shadow-sm'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="cursor-pointer px-4 py-2 text-sm font-medium rounded-2xl transition-all duration-200"
        >
            پیشرفته
        </button>

        <button
            type="button"
            @click="activeTab = 'social'"
            :class="activeTab === 'social'
            ? 'bg-orange-500 text-white shadow-sm'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            class="cursor-pointer px-4 py-2 text-sm font-medium rounded-2xl transition-all duration-200"
        >
            شبکه‌های اجتماعی
        </button>
    </div>

    <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
        {{-- SEO Title --}}
        <section class="space-y-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <label class="text-sm font-bold text-slate-800">
                    عنوان سئو
                </label>

                <div class="flex flex-wrap gap-2">
                    <template x-for="variable in titleVariables" :key="'title-' + variable.token">
                        <button
                            type="button"
                            @click="insertAtCursor('titleInput', variable.token)"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700"
                            x-text="variable.label"
                        ></button>
                    </template>
                </div>
            </div>

            <x-admin.form.input
                x-ref="titleInput"
                name="seo[title]"
                x-model="seoTitle"
                x-bind:placeholder="titleTemplate"
            />

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3 text-xs">
                    <div class="truncate text-slate-600" x-text="resolvedSeoTitle || '—'"></div>
                    <div class="shrink-0 font-medium" :class="titleScoreTextClass">
                        <span x-text="`${titleScore}%`"></span>
                        <span class="text-slate-400">•</span>
                        <span class="text-slate-500" x-text="`${resolvedSeoTitle.length} کاراکتر`"></span>
                    </div>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full transition-all duration-300"
                        :class="titleScoreBarClass"
                        :style="`width: ${titleScore}%`"
                    ></div>
                </div>
            </div>
        </section>

        {{-- Meta Description --}}
        <section class="space-y-3">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <label class="text-sm font-bold text-slate-800">
                    توضیحات متا
                </label>

                <div class="flex flex-wrap gap-2">
                    <template x-for="variable in descriptionVariables" :key="'description-' + variable.token">
                        <button
                            type="button"
                            @click="insertAtCursor('descriptionInput', variable.token)"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:-translate-y-0.5 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700"
                            x-text="variable.label"
                        ></button>
                    </template>
                </div>
            </div>

            <x-admin.form.textarea
                x-ref="descriptionInput"
                name="seo[description]"
                rows="4"
                x-model="seoDescription"
                x-bind:placeholder="descriptionTemplate"
            />

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3 text-xs">
                    <div class="truncate text-slate-600" x-text="resolvedSeoDescription || '—'"></div>
                    <div class="shrink-0 font-medium" :class="descriptionScoreTextClass">
                        <span x-text="`${descriptionScore}%`"></span>
                        <span class="text-slate-400">•</span>
                        <span class="text-slate-500" x-text="`${resolvedSeoDescription.length} کاراکتر`"></span>
                    </div>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full transition-all duration-300"
                        :class="descriptionScoreBarClass"
                        :style="`width: ${descriptionScore}%`"
                    ></div>
                </div>
            </div>
        </section>
    </div>
</div>

@push('scripts')
    <script>
        function postSeoBox(config = {}) {
            return {
                activeTab: 'general',

                title: config.title ?? '',
                excerpt: config.excerpt ?? '',

                seoTitle: config.seoTitle ?? '',
                seoDescription: config.seoDescription ?? '',

                titleTemplate: config.titleTemplate ?? '{post_title} {separator} {site_title}',
                descriptionTemplate: config.descriptionTemplate ?? 'مشاهده {post_title} در {site_title}',

                siteTitle: config.siteTitle ?? '',
                separator: config.separator ?? '|',

                variables: config.variables ?? [],

                insertAtCursor(refName, value) {
                    const el = this.$refs[refName];

                    if (!el) {
                        return;
                    }

                    const start = el.selectionStart ?? el.value.length;
                    const end = el.selectionEnd ?? el.value.length;
                    const currentValue = el.value ?? '';

                    el.value = currentValue.substring(0, start) + value + currentValue.substring(end);
                    el.dispatchEvent(new Event('input', {bubbles: true}));

                    this.$nextTick(() => {
                        el.focus();

                        const caret = start + value.length;

                        if (typeof el.setSelectionRange === 'function') {
                            el.setSelectionRange(caret, caret);
                        }
                    });
                },

                replaceVariables(template) {
                    const map = {
                        post_title: this.title ?? '',
                        site_title: this.siteTitle ?? '',
                        separator: this.separator ?? '',
                        excerpt: this.excerpt ?? '',
                    };

                    return (template ?? '')
                        .replace(/\{([a-zA-Z0-9_]+)\}/g, (full, key) => {
                            return Object.prototype.hasOwnProperty.call(map, key)
                                ? String(map[key] ?? '')
                                : '';
                        })
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                get effectiveSeoTitleTemplate() {
                    return (this.seoTitle ?? '').trim() !== ''
                        ? this.seoTitle
                        : this.titleTemplate;
                },

                get effectiveSeoDescriptionTemplate() {
                    return (this.seoDescription ?? '').trim() !== ''
                        ? this.seoDescription
                        : this.descriptionTemplate;
                },

                get resolvedSeoTitle() {
                    return this.replaceVariables(this.effectiveSeoTitleTemplate);
                },

                get resolvedSeoDescription() {
                    return this.replaceVariables(this.effectiveSeoDescriptionTemplate);
                },

                scoreByLength(length, minIdeal, maxIdeal, warningMax) {
                    if (length === 0) {
                        return 0;
                    }

                    if (length >= minIdeal && length <= maxIdeal) {
                        return 100;
                    }

                    if (length < minIdeal) {
                        return Math.max(20, Math.round((length / minIdeal) * 100));
                    }

                    if (length <= warningMax) {
                        const overflowRange = warningMax - maxIdeal;
                        const overflow = length - maxIdeal;

                        return Math.max(50, 100 - Math.round((overflow / overflowRange) * 50));
                    }

                    return 25;
                },

                get titleScore() {
                    return this.scoreByLength(this.resolvedSeoTitle.length, 35, 60, 75);
                },

                get descriptionScore() {
                    return this.scoreByLength(this.resolvedSeoDescription.length, 70, 160, 190);
                },

                scoreTextClass(score) {
                    if (score >= 85) return 'text-emerald-600';
                    if (score >= 60) return 'text-amber-600';

                    return 'text-rose-600';
                },

                scoreBarClass(score) {
                    if (score >= 85) return 'bg-emerald-500';
                    if (score >= 60) return 'bg-amber-500';

                    return 'bg-rose-500';
                },

                get titleScoreTextClass() {
                    return this.scoreTextClass(this.titleScore);
                },

                get descriptionScoreTextClass() {
                    return this.scoreTextClass(this.descriptionScore);
                },

                get titleScoreBarClass() {
                    return this.scoreBarClass(this.titleScore);
                },

                get descriptionScoreBarClass() {
                    return this.scoreBarClass(this.descriptionScore);
                },

                get titleVariables() {
                    return this.variables.filter(variable =>
                        ['{post_title}', '{site_title}', '{separator}'].includes(variable.token)
                    );
                },

                get descriptionVariables() {
                    return this.variables.filter(variable =>
                        ['{post_title}', '{site_title}', '{excerpt}'].includes(variable.token)
                    );
                },
            };
        }
    </script>
@endpush
