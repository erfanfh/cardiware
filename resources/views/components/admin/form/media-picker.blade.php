@props([
    'mode' => 'field', // field | repeater
    'name',
    'value' => [
        'id' => null,
        'path' => null,
    ],
    'previewHeight' => 'h-24',
    'buttonClass' => '',
    'layout' => 'horizontal', // horizontal | vertical
])

@php
    $isVertical = $layout === 'vertical';

    $selectedWrapperClasses = $isVertical
        ? 'flex flex-col gap-4 cursor-pointer'
        : 'flex items-center gap-4 cursor-pointer';

    $previewWrapperClasses = $isVertical
        ? 'relative w-full '.$previewHeight.' overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm'
        : 'relative w-32 '.$previewHeight.' shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm';

    $contentWrapperClasses = $isVertical
        ? 'min-w-0 w-full'
        : 'min-w-0 flex-1';
@endphp

@if($mode === 'repeater')
    <div
        class="space-y-3"
        x-on:mfip-selected.window="
            if ($event.detail.itemKey !== it.__key) return;
            it['{{ $name }}'] = {
                id: $event.detail.mediaId ?? null,
                path: $event.detail.mediaPath ?? null,
            };
        "
        x-on:mfip-cleared.window="
            if ($event.detail.itemKey !== it.__key) return;
            it['{{ $name }}'] = {
                id: null,
                path: null,
            };
        "
    >
        <div
            class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm ring-1 ring-slate-100/80 transition hover:border-slate-300 hover:shadow-md {{ $buttonClass }}">
            <button
                type="button"
                class="block w-full text-right"
                @click="$dispatch('mfip-open-requested', {
                    itemKey: it.__key,
                    selectedMediaFileId: it['{{ $name }}']?.id ?? null,
                    selectedMediaPath: it['{{ $name }}']?.path ?? null
                })"
            >
                <template x-if="it['{{ $name }}']?.path">
                    <div class="{{ $selectedWrapperClasses }}">
                        <div class="{{ $previewWrapperClasses }}">
                            <img
                                :src="it['{{ $name }}']?.path"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                                decoding="async"
                            >
                            <div
                                class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/10 to-transparent"></div>
                        </div>

                        <div class="{{ $contentWrapperClasses }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div
                                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-extrabold text-emerald-700 ring-1 ring-emerald-100">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    تصویر انتخاب شده
                                </div>

                                <div
                                    class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition group-hover:bg-slate-200">
                                    تغییر تصویر
                                </div>
                            </div>

                            <div
                                class="mt-3 truncate text-xs leading-6 text-slate-500"
                                x-text="it['{{ $name }}']?.path || 'آدرسی ثبت نشده است'"
                            ></div>
                        </div>
                    </div>
                </template>

                <template x-if="!it['{{ $name }}']?.path">
                    <div
                        class="rounded-lg cursor-pointer border border-dashed border-slate-300 bg-gradient-to-b from-slate-50 to-white p-6 text-center transition hover:border-slate-400 hover:from-slate-100 hover:to-slate-50">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 16V8m0 0-3 3m3-3 3 3"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M20 16.74A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div class="text-sm font-extrabold text-slate-800">
                            هنوز تصویری انتخاب نشده است
                        </div>

                        <div class="mt-1 text-xs leading-6 text-slate-500">
                            برای انتخاب یا تغییر تصویر کلیک کنید
                        </div>

                        <div
                            class="mt-4 inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm">
                            انتخاب تصویر
                        </div>
                    </div>
                </template>
            </button>

            <div
                class="mt-3 flex items-center justify-end border-t border-slate-100 pt-3"
                x-show="it['{{ $name }}']?.id"
            >
                <button
                    type="button"
                    class="cursor-pointer inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100"
                    @click.stop="
                        it['{{ $name }}'] = {
                            id: null,
                            path: null,
                        };
                    "
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    حذف تصویر
                </button>
            </div>
        </div>

        <input
            type="hidden"
            x-bind:name="field(i, '{{ $name }}.id')"
            x-model="it['{{ $name }}'].id"
        >

        <input
            type="hidden"
            x-bind:name="field(i, '{{ $name }}.path')"
            x-model="it['{{ $name }}'].path"
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
        <div
            class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm ring-1 ring-slate-100/80 transition hover:border-slate-300 hover:shadow-md {{ $buttonClass }}">
            <button
                type="button"
                class="block w-full text-right"
                @click="$dispatch('mfip-open-requested', {
                    itemKey: itemKey,
                    selectedMediaFileId: selectedId ?? null,
                    selectedMediaPath: selectedPath ?? null
                })"
            >
                <template x-if="selectedPath">
                    <div class="{{ $selectedWrapperClasses }}">
                        <div class="{{ $previewWrapperClasses }}">
                            <img
                                :src="selectedPath"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                                decoding="async"
                            >
                            <div
                                class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/10 to-transparent"></div>
                        </div>

                        <div class="{{ $contentWrapperClasses }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div
                                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-extrabold text-emerald-700 ring-1 ring-emerald-100">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    تصویر انتخاب شده
                                </div>

                                <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">
                                    تغییر تصویر
                                </div>
                            </div>

                            <div
                                class="mt-3 truncate text-xs leading-6 text-slate-500"
                                x-text="selectedPath || 'آدرسی ثبت نشده است'"
                            ></div>
                        </div>
                    </div>
                </template>

                <template x-if="!selectedPath">
                    <div
                        class="rounded-lg cursor-pointer border border-dashed border-slate-300 bg-gradient-to-b from-slate-50 to-white p-6 text-center transition hover:border-slate-400 hover:from-slate-100 hover:to-slate-50">
                        <div
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 16V8m0 0-3 3m3-3 3 3"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M20 16.74A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div class="text-sm font-extrabold text-slate-800">
                            هنوز تصویری انتخاب نشده است
                        </div>

                        <div class="mt-1 text-xs leading-6 text-slate-500">
                            برای انتخاب یا تغییر تصویر کلیک کنید
                        </div>

                        <div
                            class="mt-4 inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm">
                            انتخاب تصویر
                        </div>
                    </div>
                </template>
            </button>

            <div class="mt-3 flex items-center justify-end border-t border-slate-100 pt-3" x-show="selectedId">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100"
                    @click.stop="
                        selectedId = null;
                        selectedPath = null;
                    "
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    حذف تصویر
                </button>
            </div>
        </div>

        @if($name)
            <input type="hidden" name="{{ $name }}[id]" x-model="selectedId">
            <input type="hidden" name="{{ $name }}[path]" x-model="selectedPath">
        @endif
    </div>
@endif
