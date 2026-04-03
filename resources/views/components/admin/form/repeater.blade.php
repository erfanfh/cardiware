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
    class="space-y-3"
>
    <div class="flex items-center justify-between gap-3">
        <div class="text-xs text-slate-500">
            {{ $title }}
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="text-xs text-slate-500 hover:text-slate-700"
                @click="expandAll()"
            >
                باز کردن همه
            </button>

            <button
                type="button"
                class="text-xs text-slate-500 hover:text-slate-700"
                @click="collapseAll()"
            >
                بستن همه
            </button>

            <x-admin.form.button
                type="button"
                variant="soft"
                size="sm"
                @click="add()"
            >
                + {{ $addText }}
            </x-admin.form.button>
        </div>
    </div>

    <template x-if="items.length === 0">
        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">
            {{ $emptyText }}
        </div>
    </template>

    <div x-ref="list" class="space-y-3">
        <template x-for="(it, i) in items" :key="`${it.__key}-${__renderNonce}`">
            <div
                data-repeater-item
                class="rounded-2xl border border-slate-200 bg-white overflow-hidden"
            >
                <div
                    class="p-4 flex items-center justify-between gap-3 cursor-pointer"
                    @click="toggle(i)"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        @if($sortable)
                            <div
                                class="drag-handle cursor-move text-slate-400 hover:text-slate-600"
                                @click.stop
                                title="جابجایی"
                            >
                                ☰
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 truncate max-w-[520px]">
                                <span
                                    x-text="(it['{{ $headingKey }}'] && String(it['{{ $headingKey }}']).trim().length)
                                        ? it['{{ $headingKey }}']
                                        : '{{ $headingPlaceholder }}'"
                                    :class="(!it['{{ $headingKey }}'] || !String(it['{{ $headingKey }}']).trim().length)
                                        ? 'text-slate-400 font-semibold'
                                        : ''"
                                ></span>
                            </div>

                            <div
                                class="text-[11px] text-slate-500 truncate max-w-[520px]"
                                x-show="!it._open"
                                x-cloak
                            >
                                برای مشاهده/ویرایش کلیک کنید
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            class="h-9 w-9 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100"
                            @click.stop="remove(i)"
                            title="حذف"
                        >
                            ✕
                        </button>

                        <div class="text-slate-400 select-none">
                            <span x-show="it._open" x-cloak>▾</span>
                            <span x-show="!it._open" x-cloak>▸</span>
                        </div>
                    </div>
                </div>

                <div
                    class="p-4 border-t border-slate-200"
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
