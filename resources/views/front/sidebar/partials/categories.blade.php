@php
    $sidebarCategoriesWidget = app(\App\Application\System\Actions\Sidebar\GetSidebarCategoriesAction::class)->execute();
@endphp

<x-front.sidebar.sidebar-item
    :title="$sidebarCategoriesWidget['title']"
    :subtitle="$sidebarCategoriesWidget['subtitle']"
    iconName="right-to-left-list-bullet"
>
    <ul class="space-y-3">
        @foreach($sidebarCategoriesWidget['categories'] as $category)
            <li>
                <a href="{{ front_route('posts.categories', $category) }}"
                   class="flex items-center justify-between text-sm rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition dark:border-slate-700 dark:hover:bg-slate-800">
                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ $category->title }}
                    </span>

                    <span
                        class="text-[0.7rem] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        {{ $category->posts_count ?: 0 }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</x-front.sidebar.sidebar-item>
