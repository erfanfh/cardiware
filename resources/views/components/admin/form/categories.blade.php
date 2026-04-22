@props([
    'name' => 'category_ids',
    'items' => [],
    'selected' => [],
    'height' => '20rem',
    'quickCreate' => true,
    'quickCreateParentEnabled' => true,
    'quickCreateUrl' => null,
])

@php
    $normalizedItems = collect($items)
        ->map(function ($item) {
            return [
                'id' => (int) ($item['id'] ?? 0),
                'title' => trim((string) ($item['title'] ?? '')),
                'parent_id' => isset($item['parent_id']) && $item['parent_id'] !== ''
                    ? (int) $item['parent_id']
                    : null,
            ];
        })
        ->filter(fn ($item) => $item['id'] > 0 && $item['title'] !== '')
        ->values()
        ->all();

    $normalizedSelected = collect($selected)
        ->map(fn ($item) => is_array($item) ? (int) ($item['id'] ?? 0) : (int) $item)
        ->filter(fn ($id) => $id > 0)
        ->values()
        ->all();
@endphp

<div
    x-data="adminCategoriesBox({
        items: @js($normalizedItems),
        selected: @js($normalizedSelected),
        quickCreateParentEnabled: @js($quickCreateParentEnabled),
        quickCreateUrl: @js($quickCreateUrl),
        csrfToken: @js(csrf_token()),
    })"
>
    <div x-show="open" x-collapse class="space-y-4">
        <div class="space-y-3">
            <div class="relative">
                <x-admin.form.input
                    x-model.debounce.250ms="search"
                    placeholder="جستجو در دسته‌ها..."
                    class="pr-9"
                />

                <span
                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 [&_svg]:h-4 [&_svg]:w-4">
                    @include('icons.search-01')
                </span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70">
                <div class="flex items-center justify-between border-b border-slate-200 px-3 py-2">
                    <p class="text-xs font-medium text-slate-500">
                        دسته‌های موجود
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            x-show="selected.length > 0 && !isSearching"
                            type="button"
                            class="cursor-pointer text-[11px] font-medium text-slate-500 transition hover:text-slate-700"
                            @click="showSelectedOnly = !showSelectedOnly"
                            x-text="showSelectedOnly ? 'نمایش همه' : `نمایش انتخاب‌شده‌ها (${selected.length})`"
                        >
                        </button>

                        <button
                            x-show="search"
                            type="button"
                            class="cursor-pointer text-[11px] font-medium text-slate-500 transition hover:text-slate-700"
                            @click="search = ''"
                        >
                            پاک کردن جستجو
                        </button>
                    </div>
                </div>

                <div
                    x-show="showSelectedOnly && !isSearching"
                    class="border-b border-slate-200 bg-white/70 px-3 py-2 text-[11px] text-slate-500"
                >
                    فقط دسته‌های انتخاب‌شده نمایش داده می‌شوند.
                </div>

                <div class="p-2">
                    <div
                        class="relative overflow-y-auto px-1"
                        style="max-height: {{ $height }}"
                    >
                        <div
                            x-show="searchLoading"
                            x-transition.opacity
                            class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70 backdrop-blur-[1px]"
                        >
                            <div
                                class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 shadow-sm">
                                <svg class="h-4 w-4 animate-spin text-slate-500" viewBox="0 0 24 24" fill="none">
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-opacity="0.25"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        d="M22 12a10 10 0 0 0-10-10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                    ></path>
                                </svg>
                            </div>
                        </div>

                        <template x-if="!searchLoading && visibleItems.length === 0">
                            <div class="px-3 py-8 text-center text-sm text-slate-500">
                                دسته‌ای پیدا نشد.
                            </div>
                        </template>

                        <div class="space-y-1" :class="{ 'opacity-50': searchLoading }">
                            <template x-for="item in visibleItems" :key="item.id">
                                <label
                                    class="flex cursor-pointer items-center justify-between gap-3 rounded-xl px-3 py-2.5 transition hover:bg-white"
                                    :class="{ 'bg-brand-50/60': isSelected(item.id) }"
                                >
                                    <div class="flex min-w-0 items-center gap-3">
                                        <input
                                            type="checkbox"
                                            :value="item.id"
                                            :checked="isSelected(item.id)"
                                            @change="toggle(item.id)"
                                            class="h-3 w-3 cursor-pointer rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                                        >

                                        <div
                                            class="flex min-w-0 items-center gap-2"
                                            :style="isSearching || showSelectedOnly ? '' : `padding-right:${item.level * 18}px`"
                                        >
                                            <span
                                                class="truncate text-sm"
                                                :class="[
                                                    isSearching || showSelectedOnly
                                                        ? 'text-slate-700'
                                                        : (item.level === 0 ? 'font-medium text-slate-800' : 'text-slate-700')
                                                ]"
                                                x-text="item.title"
                                            ></span>
                                        </div>
                                    </div>

                                    <span
                                        x-show="isSelected(item.id)"
                                        class="shrink-0 rounded-full bg-brand-100 px-2 py-1 text-[11px] font-medium text-brand-700"
                                    >
                                        انتخاب‌شده
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($quickCreate)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <button
                    type="button"
                    class="cursor-pointer text-sm font-bold text-brand-700 transition hover:text-brand-800"
                    @click="quickCreateOpen = !quickCreateOpen"
                >
                    <span x-text="quickCreateOpen ? 'بستن افزودن سریع' : '+ افزودن دسته جدید'"></span>
                </button>

                <div x-show="quickCreateOpen" x-collapse class="mt-4 space-y-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-admin.form.input
                                x-model="newCategoryTitle"
                                placeholder="عنوان دسته"
                            />
                        </div>

                        <template x-if="quickCreateParentEnabled">
                            <div class="sm:col-span-2">
                                <x-admin.form.async-select
                                    x-model="newCategoryParentId"
                                    placeholder="بدون والد"
                                    :multiple="false"
                                    :search-url="route('admin.posts.categories.ajax-search')"
                                />
                            </div>
                        </template>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin.form.button
                            type="button"
                            variant="solid"
                            size="sm"
                            @click="addQuickCategory()"
                            x-bind:disabled="quickCreateLoading"
                        >
                            <span x-show="!quickCreateLoading">افزودن دسته</span>
                            <span x-show="quickCreateLoading">در حال ایجاد...</span>
                        </x-admin.form.button>
                    </div>

                    <p
                        x-show="quickCreateError"
                        x-text="quickCreateError"
                        class="text-xs font-medium text-rose-600"
                    ></p>
                </div>
            </div>
        @endif

        <template x-for="id in selected" :key="'hidden-' + id">
            <input type="hidden" name="{{ $name }}[]" :value="id">
        </template>
    </div>
</div>

@once
    <script>
        function adminCategoriesBox(config) {
            return {
                open: true,
                quickCreateOpen: false,
                search: '',
                searchLoading: false,
                searchLoadingTimeout: null,
                showSelectedOnly: false,
                newCategoryTitle: '',
                newCategoryParentId: '',
                quickCreateError: '',
                quickCreateLoading: false,
                quickCreateParentEnabled: config.quickCreateParentEnabled ?? true,
                quickCreateUrl: config.quickCreateUrl ?? null,
                csrfToken: config.csrfToken ?? '',
                items: config.items ?? [],
                selected: config.selected ?? [],

                init() {
                    this.$watch('search', (value) => {
                        if (value.trim() !== '') {
                            this.showSelectedOnly = false;
                        }

                        this.startSearchLoading();
                    });
                },

                get isSearching() {
                    return this.search.trim() !== '';
                },

                get treeItems() {
                    return this.buildTreeItems(this.items);
                },

                get flatItems() {
                    return [...this.items].sort((a, b) =>
                        String(a.title ?? '').localeCompare(String(b.title ?? ''), 'fa')
                    );
                },

                get visibleItems() {
                    const term = this.search.trim().toLowerCase();

                    if (term) {
                        return this.flatItems
                            .filter(item => (item.title || '').toLowerCase().includes(term))
                            .map(item => ({
                                ...item,
                                level: 0,
                            }));
                    }

                    if (this.showSelectedOnly) {
                        return this.selected
                            .map(selectedId => this.treeItems.find(item => Number(item.id) === Number(selectedId)))
                            .filter(Boolean)
                            .map(item => ({
                                ...item,
                                level: 0,
                            }));
                    }

                    return this.treeItems;
                },

                get parentOptions() {
                    return this.treeItems.map(item => ({
                        id: item.id,
                        label: `${'— '.repeat(item.level)}${item.title}`,
                    }));
                },

                isSelected(id) {
                    return this.selected.includes(Number(id));
                },

                toggle(id) {
                    id = Number(id);

                    if (this.isSelected(id)) {
                        this.selected = this.selected.filter(item => item !== id);

                        if (this.showSelectedOnly && this.selected.length === 0) {
                            this.showSelectedOnly = false;
                        }

                        return;
                    }

                    this.selected = [...this.selected, id];
                },

                startSearchLoading() {
                    if (this.search.trim() === '') {
                        this.searchLoading = false;

                        if (this.searchLoadingTimeout) {
                            clearTimeout(this.searchLoadingTimeout);
                        }

                        return;
                    }

                    this.searchLoading = true;

                    if (this.searchLoadingTimeout) {
                        clearTimeout(this.searchLoadingTimeout);
                    }

                    this.searchLoadingTimeout = setTimeout(() => {
                        this.searchLoading = false;
                    }, 220);
                },

                async addQuickCategory() {
                    this.quickCreateError = '';

                    const title = this.newCategoryTitle.trim();
                    const parentId = this.newCategoryParentId !== ''
                        ? Number(this.newCategoryParentId)
                        : null;

                    if (!title) {
                        this.quickCreateError = 'عنوان دسته را وارد کنید.';
                        return;
                    }

                    if (!this.quickCreateUrl) {
                        this.quickCreateError = 'آدرس ساخت سریع دسته تنظیم نشده است.';
                        return;
                    }

                    this.quickCreateLoading = true;

                    try {
                        const response = await fetch(this.quickCreateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({
                                title: title,
                                parent_id: parentId,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            this.quickCreateError =
                                data?.message
                                ?? data?.errors?.title?.[0]
                                ?? data?.errors?.parent_id?.[0]
                                ?? 'ایجاد دسته با خطا مواجه شد.';

                            return;
                        }

                        const item = data.item;

                        const alreadyExists = this.items.some(existing => Number(existing.id) === Number(item.id));

                        if (!alreadyExists) {
                            this.items = [...this.items, item];
                        }

                        if (!this.isSelected(item.id)) {
                            this.selected = [...this.selected, Number(item.id)];
                        }

                        this.newCategoryTitle = '';
                        this.newCategoryParentId = '';
                        this.quickCreateOpen = false;
                        this.search = '';
                        this.showSelectedOnly = false;
                    } catch (error) {
                        this.quickCreateError = 'ارتباط با سرور برقرار نشد.';
                    } finally {
                        this.quickCreateLoading = false;
                    }
                },

                buildTreeItems(items) {
                    const normalizedItems = items.map(item => ({
                        id: Number(item.id),
                        title: String(item.title ?? '').trim(),
                        parent_id: item.parent_id !== null && item.parent_id !== undefined && item.parent_id !== ''
                            ? Number(item.parent_id)
                            : null,
                    }));

                    const childrenMap = new Map();

                    normalizedItems.forEach(item => {
                        const key = item.parent_id ?? 0;

                        if (!childrenMap.has(key)) {
                            childrenMap.set(key, []);
                        }

                        childrenMap.get(key).push(item);
                    });

                    childrenMap.forEach(children => {
                        children.sort((a, b) => a.title.localeCompare(b.title, 'fa'));
                    });

                    const result = [];

                    const walk = (parentId = null, level = 0) => {
                        const key = parentId ?? 0;
                        const children = childrenMap.get(key) ?? [];

                        children.forEach(child => {
                            result.push({
                                ...child,
                                level: level,
                            });

                            walk(child.id, level + 1);
                        });
                    };

                    walk(null, 0);

                    return result;
                },
            };
        }
    </script>
@endonce
