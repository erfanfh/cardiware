@props([
    'mode' => 'field', // field | repeater
    'name' => null,    // فقط برای field
    'value' => [
        'id' => null,
        'path' => null,
    ],

    // فقط برای repeater
    'idField' => 'image_id',
    'pathField' => 'image_path',

    'label' => 'تصویر',
    'previewHeight' => 'h-24',
    'buttonClass' => '',
])

@if($mode === 'repeater')
    <div
        class="space-y-3"
        x-on:mfip-selected.window="
            if ($event.detail.itemKey !== it.__key) return;
            it['{{ $idField }}'] = $event.detail.mediaId ?? null;
            it['{{ $pathField }}'] = $event.detail.mediaPath ?? null;
        "
        x-on:mfip-cleared.window="
            if ($event.detail.itemKey !== it.__key) return;
            it['{{ $idField }}'] = null;
            it['{{ $pathField }}'] = null;
        "
    >
        <button
            type="button"
            class="w-full rounded-2xl border border-slate-200 bg-white p-3 text-right transition hover:border-slate-300 hover:bg-slate-50 {{ $buttonClass }}"
            @click="$dispatch('mfip-open-singleton', {
                itemKey: it.__key,
                selectedMediaFileId: it['{{ $idField }}'] ?? null
            })"
        >
            <template x-if="it['{{ $pathField }}']">
                <div class="flex items-center gap-3">
                    <div
                        class="w-32 {{ $previewHeight }} rounded-xl border border-slate-200 bg-slate-50 shrink-0 overflow-hidden">
                        <img
                            :src="it['{{ $pathField }}']"
                            alt=""
                            class="h-full w-full object-cover"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-bold text-slate-800">
                                {{ $label }}
                            </div>

                            <div class="shrink-0 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">
                                تغییر تصویر
                            </div>
                        </div>

                        <div
                            class="mt-1 text-xs text-slate-500 truncate"
                            x-text="it['{{ $pathField }}'] || 'آدرسی ثبت نشده است'"
                        ></div>
                    </div>
                </div>
            </template>

            <template x-if="!it['{{ $pathField }}']">
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center">
                    <div
                        class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 16V8m0 0-3 3m3-3 3 3" stroke="currentColor" stroke-width="1.8"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 16.74A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25" stroke="currentColor"
                                  stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="text-sm font-bold text-slate-700">
                        هنوز تصویری انتخاب نشده است
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        برای انتخاب تصویر کلیک کنید
                    </div>
                </div>
            </template>
        </button>

        <div class="flex items-center gap-2" x-show="it['{{ $idField }}']">
            <button
                type="button"
                class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100"
                @click="
                    it['{{ $idField }}'] = null;
                    it['{{ $pathField }}'] = null;
                "
            >
                حذف تصویر
            </button>
        </div>

        <input
            type="hidden"
            x-bind:name="field(i, '{{ $idField }}')"
            x-model="it['{{ $idField }}']"
        >

        <input
            type="hidden"
            x-bind:name="field(i, '{{ $pathField }}')"
            x-model="it['{{ $pathField }}']"
        >
    </div>
@else
    <div
        x-data="{
            selectedId: @js(data_get($value, 'id')),
            selectedPath: @js(data_get($value, 'path')),
            itemKey: 'field-{{ md5(($name ?? 'media').'-'.uniqid('', true)) }}',
        }"
        class="space-y-3"
        x-on:mfip-selected.window="
            if ($event.detail.itemKey !== itemKey) return;
            selectedId = $event.detail.mediaId ?? null;
            selectedPath = $event.detail.mediaPath ?? null;
        "
        x-on:mfip-cleared.window="
            if ($event.detail.itemKey !== itemKey) return;
            selectedId = null;
            selectedPath = null;
        "
    >
        <button
            type="button"
            class="w-full rounded-2xl border border-slate-200 bg-white p-3 text-right transition hover:border-slate-300 hover:bg-slate-50 {{ $buttonClass }}"
            @click="$dispatch('mfip-open-singleton', {
                itemKey: itemKey,
                selectedMediaFileId: selectedId ?? null
            })"
        >
            <template x-if="selectedPath">
                <div class="flex items-center gap-3">
                    <div
                        class="w-32 {{ $previewHeight }} rounded-xl border border-slate-200 bg-slate-50 shrink-0 overflow-hidden">
                        <img
                            :src="selectedPath"
                            alt=""
                            class="h-full w-full object-cover"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-bold text-slate-800">
                                {{ $label }}
                            </div>

                            <div class="shrink-0 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">
                                تغییر تصویر
                            </div>
                        </div>

                        <div
                            class="mt-1 text-xs text-slate-500 truncate"
                            x-text="selectedPath || 'آدرسی ثبت نشده است'"
                        ></div>
                    </div>
                </div>
            </template>

            <template x-if="!selectedPath">
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center">
                    <div
                        class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 16V8m0 0-3 3m3-3 3 3" stroke="currentColor" stroke-width="1.8"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 16.74A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25" stroke="currentColor"
                                  stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="text-sm font-bold text-slate-700">
                        هنوز تصویری انتخاب نشده است
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        برای انتخاب تصویر کلیک کنید
                    </div>
                </div>
            </template>
        </button>

        <div class="flex items-center gap-2" x-show="selectedId">
            <button
                type="button"
                class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition hover:bg-rose-100"
                @click="
                    selectedId = null;
                    selectedPath = null;
                "
            >
                حذف تصویر
            </button>
        </div>

        @if($name)
            <input type="hidden" name="{{ $name }}[id]" x-model="selectedId">
            <input type="hidden" name="{{ $name }}[path]" x-model="selectedPath">
        @endif
    </div>
@endif
