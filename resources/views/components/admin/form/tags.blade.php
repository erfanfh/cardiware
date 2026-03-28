@props([
    'existingName' => 'tags[existing]',
    'newName' => 'tags[new]',
    'selected' => [],
    'placeholder' => 'افزودن برچسب جدید',
    'help' => 'برچسب‌ها را با Enter یا کاما جدا کنید.',
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
    })"
    class="space-y-4"
>
    <div class="flex flex-col gap-2 sm:flex-row">
        <div class="flex-1">
            <x-admin.form.input
                x-model="input"
                placeholder="{{ $placeholder }}"
                @keydown.enter.prevent="commitInput()"
            />
        </div>

        <x-admin.button
            @click="commitInput()"
            variant="soft"
            size="sm"
            class="rounded-2xl h-10"
        >
            افزودن
        </x-admin.button>
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
                class="text-xs cursor-pointer text-rose-600 transition hover:text-rose-700"
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
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700">
                        <span x-text="tag.name"></span>

                        <button
                            type="button"
                            @click="removeTag(tag.key)"
                            class="inline-flex cursor-pointer h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-rose-50 hover:text-rose-600"
                            :aria-label="`حذف ${tag.name}`"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none">
                                <path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="1.8"
                                      stroke-linecap="round"/>
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>
    </div>

    <template x-for="tag in selected" :key="`hidden-existing-${tag.key}`">
        <template x-if="tag.id">
            <input type="hidden" name="{{ $existingName }}[]" :value="tag.id">
        </template>
    </template>

    <template x-for="tag in selected" :key="`hidden-new-${tag.key}`">
        <template x-if="!tag.id">
            <input type="hidden" name="{{ $newName }}[]" :value="tag.name">
        </template>
    </template>
</div>

@once
    @push('scripts')
        <script>
            function adminTagsBox(config = {}) {
                return {
                    input: '',

                    selected: (config.selected || []).map(item => ({
                        id: item.id ?? null,
                        name: String(item.name || '').trim(),
                        key: adminTagBoxKey(item.id ?? null, String(item.name || '').trim()),
                    })),

                    normalize(text) {
                        return String(text || '')
                            .replace(/\s+/g, ' ')
                            .trim();
                    },

                    normalizedForCompare(text) {
                        return this.normalize(text).toLocaleLowerCase('fa');
                    },

                    exists(name) {
                        const needle = this.normalizedForCompare(name);

                        return this.selected.some(tag =>
                            this.normalizedForCompare(tag.name) === needle
                        );
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
                    },

                    removeTag(key) {
                        this.selected = this.selected.filter(tag => tag.key !== key);
                    },

                    clearAll() {
                        this.selected = [];
                    },
                }
            }

            function adminTagBoxKey(id, name) {
                return `${id ?? 'new'}::${String(name).trim().toLocaleLowerCase('fa')}`;
            }
        </script>
    @endpush
@endonce
