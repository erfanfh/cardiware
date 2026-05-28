@props([
    'existingName' => 'tags[existing]',
    'newName' => 'tags[new]',
    'selected' => [],
    'placeholder' => 'افزودن برچسب جدید',
    'help' => 'برچسب‌ها را با Enter یا کاما جدا کنید.',
    'searchUrl' => null,
])

@php
    $normalizedSelected = collect($selected)
        ->map(fn ($item) => [
            'id' => $item['id'] ?? null,
            'name' => trim((string) ($item['name'] ?? '')),
        ])
        ->filter(fn ($item) => $item['name'] !== '')
        ->values()
        ->all();
@endphp

<div
    x-data="adminTagsBox({
        selected: @js($normalizedSelected),
        searchUrl: @js($searchUrl),
        existingName: @js($existingName . '[]'),
        newName: @js($newName . '[]'),
    })"
    @click.outside="closeSuggestions()"
    class="space-y-4"
>
    <div class="flex flex-col gap-2 sm:flex-row">
        <div class="relative flex-1">
            <x-admin.form.input
                x-model="input"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                @input="queueSearch()"
                @focus="queueSearch()"
                @keydown.enter.prevent="commitInput()"
                @keydown.escape.prevent="closeSuggestions()"
            />

            <div
                x-cloak
                x-show="suggestionsOpen"
                class="absolute right-0 left-0 top-full z-30 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60"
            >
                <template x-if="isSearching">
                    <div class="px-4 py-3 text-xs text-slate-500">
                        در حال جستجو...
                    </div>
                </template>

                <template x-if="!isSearching && suggestions.length === 0 && normalizedInput().length >= 2">
                    <div class="px-4 py-3 text-xs text-slate-400">
                        برچسبی با این عنوان پیدا نشد. با Enter می‌توانید آن را به عنوان برچسب جدید اضافه کنید.
                    </div>
                </template>

                <template x-if="!isSearching && suggestions.length > 0">
                    <div class="max-h-56 overflow-y-auto p-1">
                        <template x-for="tag in suggestions" :key="tag.id">
                            <button
                                type="button"
                                @click="selectExistingTag(tag)"
                                class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-right transition hover:bg-slate-50"
                            >
                                <span class="text-sm font-medium text-slate-700" x-text="tag.name"></span>

                                <span
                                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                    موجود
                                </span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <x-admin.form.button
            type="button"
            @click="commitInput()"
            variant="soft"
            size="sm"
            class="h-10 rounded-2xl"
        >
            افزودن
        </x-admin.form.button>
    </div>

    <p class="text-xs text-slate-500">
        {{ $help }}
    </p>

    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-700">برچسب‌های انتخاب‌شده</span>

            <button
                type="button"
                x-show="selected.length > 0"
                @click="clearAll()"
                class="cursor-pointer text-xs text-rose-600 transition hover:text-rose-700"
            >
                حذف همه
            </button>
        </div>

        <div class="min-h-[84px] rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-3">
            <template x-if="selected.length === 0">
                <div class="flex min-h-[56px] items-center justify-center text-xs text-slate-400">
                    هنوز برچسبی انتخاب نشده است.
                </div>
            </template>

            <div class="flex flex-wrap gap-2">
                <template x-for="tag in selected" :key="tag.key">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700"
                    >
                        <span x-text="tag.name"></span>

                        <span
                            x-show="tag.id"
                            class="rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700"
                        >
                            موجود
                        </span>

                        <span
                            x-show="!tag.id"
                            class="rounded-full bg-sky-50 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700"
                        >
                            جدید
                        </span>

                        <button
                            type="button"
                            @click="removeTag(tag.key)"
                            class="inline-flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-rose-50 hover:text-rose-600"
                            :aria-label="`حذف ${tag.name}`"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M6 6L14 14M14 6L6 14"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>
    </div>

    <template x-for="tag in selected" :key="`hidden-${tag.key}`">
        <input
            type="hidden"
            :name="tag.id ? existingName : newName"
            :value="tag.id ? tag.id : tag.name"
        >
    </template>
</div>

@once
    @push('scripts')
        <script>
            function adminTagsBox(config = {}) {
                return {
                    input: '',
                    searchUrl: config.searchUrl || null,
                    existingName: config.existingName || 'tags[existing][]',
                    newName: config.newName || 'tags[new][]',

                    selected: (config.selected || []).map(item => {
                        const name = String(item.name || '').trim();

                        return {
                            id: item.id ?? null,
                            name,
                            key: adminTagBoxKey(item.id ?? null, name),
                        };
                    }),

                    suggestions: [],
                    suggestionsOpen: false,
                    isSearching: false,
                    searchTimer: null,
                    abortController: null,

                    normalize(text) {
                        return String(text || '')
                            .replace(/\s+/g, ' ')
                            .trim();
                    },

                    normalizedInput() {
                        return this.normalize(this.input);
                    },

                    normalizedForCompare(text) {
                        return this.normalize(text).toLocaleLowerCase('fa');
                    },

                    cleanSearchLabel(label, value = null) {
                        let text = this.normalize(label);

                        if (!text) {
                            return '';
                        }

                        if (value !== null && value !== undefined && String(value).trim() !== '') {
                            const escapedValue = String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

                            text = text
                                .replace(new RegExp(`\\s*\\(${escapedValue}\\)\\s*$`), '')
                                .replace(new RegExp(`\\s*\\（${escapedValue}\\）\\s*$`), '')
                                .replace(new RegExp(`\\s*\\[${escapedValue}\\]\\s*$`), '')
                                .replace(new RegExp(`\\s*\\{${escapedValue}\\}\\s*$`), '');
                        }

                        return this.normalize(text);
                    },

                    exists(name) {
                        const needle = this.normalizedForCompare(name);

                        return this.selected.some(tag =>
                            this.normalizedForCompare(tag.name) === needle
                        );
                    },

                    existsById(id) {
                        if (id === null || id === undefined || String(id).trim() === '') {
                            return false;
                        }

                        return this.selected.some(tag =>
                            tag.id !== null &&
                            tag.id !== undefined &&
                            String(tag.id) === String(id)
                        );
                    },

                    queueSearch() {
                        clearTimeout(this.searchTimer);

                        const query = this.normalizedInput();

                        if (!this.searchUrl || query.length < 2) {
                            this.suggestions = [];
                            this.suggestionsOpen = false;
                            this.isSearching = false;

                            if (this.abortController) {
                                this.abortController.abort();
                                this.abortController = null;
                            }

                            return;
                        }

                        this.suggestions = [];
                        this.suggestionsOpen = true;
                        this.isSearching = true;

                        if (this.abortController) {
                            this.abortController.abort();
                            this.abortController = null;
                        }

                        this.searchTimer = setTimeout(() => {
                            this.searchExistingTags();
                        }, 300);
                    },

                    async searchExistingTags() {
                        const query = this.normalizedInput();

                        if (!this.searchUrl || query.length < 2) {
                            this.suggestions = [];
                            this.suggestionsOpen = false;
                            this.isSearching = false;
                            return;
                        }

                        if (this.abortController) {
                            this.abortController.abort();
                        }

                        this.abortController = new AbortController();
                        this.isSearching = true;
                        this.suggestionsOpen = true;

                        try {
                            const url = new URL(this.searchUrl, window.location.origin);
                            url.searchParams.set('q', query);

                            const response = await fetch(url.toString(), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                signal: this.abortController.signal,
                            });

                            if (!response.ok) {
                                this.suggestions = [];
                                return;
                            }

                            const data = await response.json();

                            if (query !== this.normalizedInput()) {
                                return;
                            }

                            this.suggestions = (data.items || [])
                                .map(item => {
                                    const id = item.value ?? null;
                                    const name = this.cleanSearchLabel(item.label, id);

                                    return {
                                        id,
                                        name,
                                        key: adminTagBoxKey(id, name),
                                    };
                                })
                                .filter(item => item.id && item.name)
                                .filter(item => !this.existsById(item.id))
                                .filter(item => !this.exists(item.name));
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                this.suggestions = [];
                            }
                        } finally {
                            if (query === this.normalizedInput()) {
                                this.isSearching = false;
                            }
                        }
                    },

                    selectExistingTag(tag) {
                        const name = this.normalize(tag.name);

                        if (!tag.id || !name || this.existsById(tag.id) || this.exists(name)) {
                            this.input = '';
                            this.closeSuggestions();
                            return;
                        }

                        this.selected.push({
                            id: tag.id,
                            name,
                            key: adminTagBoxKey(tag.id, name),
                        });

                        this.input = '';
                        this.closeSuggestions();
                    },

                    commitInput() {
                        const raw = this.input;

                        if (!raw.trim()) {
                            return;
                        }

                        const parts = raw
                            .split(/[،,]/)
                            .map(item => this.normalize(item))
                            .filter(Boolean);

                        parts.forEach(name => {
                            if (!this.exists(name)) {
                                this.selected.push({
                                    id: null,
                                    name,
                                    key: adminTagBoxKey(null, name),
                                });
                            }
                        });

                        this.input = '';
                        this.closeSuggestions();
                    },

                    removeTag(key) {
                        this.selected = this.selected.filter(tag => tag.key !== key);
                    },

                    clearAll() {
                        this.selected = [];
                    },

                    closeSuggestions() {
                        this.suggestionsOpen = false;
                        this.suggestions = [];
                        this.isSearching = false;

                        if (this.abortController) {
                            this.abortController.abort();
                            this.abortController = null;
                        }
                    },
                }
            }

            function adminTagBoxKey(id, name) {
                return `${id ?? 'new'}::${String(name).trim().toLocaleLowerCase('fa')}`;
            }
        </script>
    @endpush
@endonce
