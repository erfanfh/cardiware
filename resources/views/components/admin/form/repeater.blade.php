@props([
    'items' => [],
    'name' => 'items',
    'defaultItem' => [],
    'title' => null,
    'addText' => 'افزودن',
    'emptyText' => 'موردی ثبت نشده',
    'sortable' => true,
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
    {{-- header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="text-xs text-slate-500">
            {{ $title }}
        </div>

        <div class="flex items-center gap-2">
            <button type="button" class="text-xs text-slate-500 hover:text-slate-700" @click="expandAll()">
                باز کردن همه
            </button>
            <button type="button" class="text-xs text-slate-500 hover:text-slate-700" @click="collapseAll()">
                بستن همه
            </button>

            <x-admin.button type="button" variant="soft" size="sm" @click="add()">
                + {{ $addText }}
            </x-admin.button>
        </div>
    </div>

    {{-- empty --}}
    <template x-if="items.length === 0">
        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">
            {{ $emptyText }}
        </div>
    </template>

    {{-- list --}}
    <div x-ref="list" class="space-y-3">
        <template x-for="(it, i) in items" :key="it.__key">
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                {{-- item header --}}
                <div class="p-4 flex items-center justify-between gap-3 cursor-pointer" @click="toggle(i)">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="drag-handle cursor-move text-slate-400" @click.stop title="جابجایی">
                            ☰
                        </div>

                        <div class="text-xs font-extrabold text-slate-700">
                            مورد <span x-text="i + 1"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                                class="h-9 w-9 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100"
                                @click.stop="remove(i)" title="حذف">
                            ✕
                        </button>

                        <div class="text-slate-400 select-none">
                            <span x-show="it._open" x-cloak>▾</span>
                            <span x-show="!it._open" x-cloak>▸</span>
                        </div>
                    </div>
                </div>

                {{-- item body --}}
                <div class="p-4 border-t border-slate-200"
                     x-show="it._open"
                     x-cloak
                     x-init="setCurrent(i)"
                >
                    {{ $slot }}

                    {{-- sort_order --}}
                    <input type="hidden" x-bind:name="field('sort_order')" x-model="it.sort_order">
                </div>
            </div>
        </template>
    </div>
</div>
