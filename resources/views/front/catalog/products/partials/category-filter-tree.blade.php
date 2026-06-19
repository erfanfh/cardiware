@foreach($categories as $category)
    @php
        $isSelected = in_array($category->id, $selectedCategories ?? [], true);
        $children = $category->childrenRecursive ?? collect();
    @endphp

    <div>
        <label
            class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border px-3 py-3 transition
            {{ $isSelected
                ? 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-800 dark:bg-brand-900/20 dark:text-brand-300'
                : 'border-slate-200 bg-white text-slate-700 hover:border-brand-100 hover:bg-brand-50/50 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200 dark:hover:border-brand-700 dark:hover:bg-slate-800/40' }}"
            style="margin-right: {{ ($level ?? 0) * 14 }}px"
        >
            <span class="flex min-w-0 items-center gap-2">
                <input
                    type="checkbox"
                    name="categories[]"
                    value="{{ $category->id }}"
                    @checked($isSelected)
                    class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500
                    dark:border-slate-600 dark:bg-slate-800 dark:focus:ring-brand-400"
                >

                <span class="truncate text-xs font-black">
                    {{ $category->title }}
                </span>
            </span>

            @if($children->count())
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-400
                    dark:bg-slate-800 dark:text-slate-500">
                    {{ $children->count() }}
                </span>
            @endif
        </label>

        @if($children->count())
            <div class="mt-2 space-y-2">
                @include('front.catalog.products.partials.category-filter-tree', [
                    'categories' => $children,
                    'level' => ($level ?? 0) + 1,
                    'selectedCategories' => $selectedCategories,
                ])
            </div>
        @endif
    </div>
@endforeach
