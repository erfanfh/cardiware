@props([
    'items' => [],
    'name' => 'items',
    'defaultItem' => [],
    'title' => null,
    'addText' => 'افزودن',
    'emptyText' => 'هنوز آیتمی اضافه نشده است.',
    'sortable' => true,
    'headingKey' => 'title',
    'headingPlaceholder' => 'بدون عنوان',
])

<div
    x-data="mwRepeater({
        items: @js($items),
        name: @js($name),
        defaultItem: @js($defaultItem),
        sortable: {{ $sortable ? 'true' : 'false' }},
    })"
    class="space-y-4"
>
    {{--
        Keep this before every indexed field. When the repeater is empty it makes
        the field present in the request; when rows exist, PHP's form parser lets
        the later indexed inputs replace this empty scalar with the submitted array.
    --}}
    <input type="hidden" x-bind:name="name" value="" data-repeater-empty-value>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm font-semibold text-slate-700">
            {{ $title }}
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-admin.form.button
                type="button"
                variant="secondary"
                size="sm"
                @click="expandAll()"
                class="text-slate-600 font-normal"
            >
                <span class="[&_svg]:w-3 [&_svg]:h-3">
                    @include('icons.arrow-down-01')
                </span>
                <span>باز کردن همه</span>
            </x-admin.form.button>

            <x-admin.form.button
                type="button"
                variant="secondary"
                size="sm"
                @click="collapseAll()"
                class="text-slate-600 font-normal"
            >
                <span class="[&_svg]:w-3 [&_svg]:h-3">
                    @include('icons.arrow-right-01')
                </span>
                <span>بستن همه</span>
            </x-admin.form.button>

            <x-admin.form.button
                type="button"
                variant="soft"
                size="sm"
                @click="add('start')"
                title="{{ $addText }} به بالای لیست"
                aria-label="{{ $addText }} به بالای لیست"
            >
                <span class="[&_svg]:w-3 [&_svg]:h-3">
                    @include('icons.plus-sign')
                </span>
                <span>افزودن بالا</span>
            </x-admin.form.button>

            <x-admin.form.button
                type="button"
                variant="soft"
                size="sm"
                @click="add('end')"
                title="{{ $addText }} به پایین لیست"
                aria-label="{{ $addText }} به پایین لیست"
            >
                <span class="[&_svg]:w-3 [&_svg]:h-3">
                    @include('icons.plus-sign')
                </span>
                <span>افزودن پایین</span>
            </x-admin.form.button>
        </div>
    </div>

    <template x-if="items.length === 0">
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-6 text-sm text-slate-500">
            {{ $emptyText }}
        </div>
    </template>

    <div x-ref="list" class="space-y-3">
        <template x-for="(it, i) in items" :key="`${it.__key}-${__renderNonce}`">
            <div
                data-repeater-item
                class="rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300"
            >
                <div
                    class="flex items-center justify-between gap-3 px-4 py-3.5 cursor-pointer"
                    @click="toggle(i)"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        @if($sortable)
                            <div
                                class="drag-handle flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-600 cursor-move"
                                @click.stop
                                title="جابجایی"
                            >
                                <span class="text-sm leading-none">⋮⋮</span>
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-slate-800 max-w-[520px]">
                                <span
                                    x-text="(it['{{ $headingKey }}'] && String(it['{{ $headingKey }}']).trim().length)
                                        ? it['{{ $headingKey }}']
                                        : '{{ $headingPlaceholder }}'"
                                    :class="(!it['{{ $headingKey }}'] || !String(it['{{ $headingKey }}']).trim().length)
                                        ? 'text-slate-400 font-medium'
                                        : ''"
                                ></span>
                            </div>

                            <div
                                class="mt-1 text-[11px] text-slate-500 truncate max-w-[520px]"
                                x-show="!it._open"
                                x-cloak
                            >
                                برای مشاهده یا ویرایش کلیک کنید
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                            @click.stop="remove(i)"
                            title="حذف"
                        >
                            <span class="text-sm leading-none">✕</span>
                        </button>

                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition"
                        >
                            <span x-show="it._open" x-cloak class="[&_svg]:w-3 [&_svg]:h-3">
                                @include('icons.arrow-down-01')
                            </span>
                            <span x-show="!it._open" x-cloak class="[&_svg]:w-3 [&_svg]:h-3">
                                @include('icons.arrow-right-01')
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="border-t border-slate-100 bg-slate-50/50 px-4 py-4"
                    x-show="it._open"
                    x-cloak
                >
                    {{ $slot }}

                    <input
                        type="hidden"
                        x-bind:name="field(i, 'sort_order')"
                        x-model="it.sort_order"
                    >
                </div>
            </div>
        </template>
    </div>
</div>
