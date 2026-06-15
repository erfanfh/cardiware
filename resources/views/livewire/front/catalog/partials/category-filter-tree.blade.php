@props([
    'categories',
    'level' => 0,
])

<div class="space-y-2">
    @foreach($categories as $category)
        @php
            $hasChildren = $category->childrenRecursive->isNotEmpty();
            $isSelected = in_array($category->id, $selectedCategories ?? [], true);
        @endphp

        <div
            x-data="{ open: {{ $isSelected ? 'true' : 'false' }} }"
            class="rounded-2xl border border-slate-200/85 bg-slate-100/85 transition hover:border-brand-200 hover:bg-brand-50"
        >
            <div class="flex items-center gap-2 px-3 py-3">
                <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-3">
                    <input
                        type="checkbox"
                        value="{{ $category->id }}"
                        wire:model.live="selectedCategories"
                        class="h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    >

                    <span class="truncate text-sm font-bold text-slate-700">
                        {{ $category->title }}
                    </span>
                </label>

                @if($hasChildren)
                    <button
                        type="button"
                        x-on:click="open = ! open"
                        class="grid h-7 w-7 shrink-0 cursor-pointer place-items-center rounded-xl bg-white text-xs font-black text-slate-500 transition hover:bg-brand-100 hover:text-brand-700"
                        aria-label="باز کردن زیر دسته‌ها"
                    >
                        <span x-show="! open">+</span>
                        <span x-show="open">−</span>
                    </button>
                @else
                    <span class="h-7 w-7 shrink-0"></span>
                @endif
            </div>

            @if($hasChildren)
                <div
                    x-show="open"
                    x-collapse
                    class="border-t border-slate-200 bg-white/55 px-3 py-3 rounded-b-2xl"
                >
                    <div
                        class="space-y-2"
                        style="padding-right: {{ min(($level + 1) * 12, 36) }}px"
                    >
                        @include('livewire.front.catalog.partials.category-filter-tree', [
                            'categories' => $category->childrenRecursive,
                            'level' => $level + 1,
                            'selectedCategories' => $selectedCategories,
                        ])
                    </div>
                </div>
            @endif
        </div>
    @endforeach
</div>
