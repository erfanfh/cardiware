@php
    $categories = \App\Domain\Taxonomy\Models\TaxonomyTerm::categories()
        ->whereIn('id', collect(get_setting('sidebarWidgets', 'categories_items'))->pluck('category_id')->toArray())
        ->select(['id', 'title', 'path'])
        ->withCount('posts')
        ->get();
@endphp

<x-front.sidebar.sidebar-item
    :title="get_setting('sidebarWidgets', 'categories_title')"
    :subtitle="get_setting('sidebarWidgets', 'categories_subtitle')"
    iconName="right-to-left-list-bullet"
>
    <ul class="space-y-3">
        @foreach(get_setting('sidebarWidgets', 'categories_items') ?? [] as $categoryItem)
            @php
                $category = $categories->find($categoryItem['category_id']);
                if(!$category) continue;
            @endphp
            <li>
                <a href="{{ route('categories', $category) }}"
                   class="flex items-center justify-between text-sm rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="font-semibold text-slate-800">{{ $category->title }}</span>
                    <span
                        class="text-[0.7rem] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">{{ $category->posts_count ?: 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</x-front.sidebar.sidebar-item>
