<x-front.sidebar.sidebar-item
    :title="get_setting('sidebarWidgets', 'tools_title')"
    :subtitle="get_setting('sidebarWidgets', 'tools_subtitle')"
    iconName="dashboard-square-03"
>
    <div class="grid grid-cols-2 gap-3">
        @foreach(get_setting('sidebarWidgets', 'tools_items') as $item)
            <a href="{{ $item['url'] }}"
               class="rounded-2xl border border-slate-200 bg-white p-2 hover:bg-slate-50 hover:shadow-sm transition dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800 dark:hover:shadow-black/20">
                <div class="flex items-center gap-3">
                    <div
                        class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center overflow-hidden dark:border-slate-700 dark:bg-slate-900">
                        <img class="w-full h-full object-cover" src="{{ $item['image']['path'] }}"
                             title="{{ $item['title'] }}"
                             alt="{{ $item['title'] }}">
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold truncate dark:text-slate-100">{{ $item['title'] }}</div>
                        <div
                            class="text-[0.65rem] mt-0.5 text-slate-500 truncate dark:text-slate-400">{{ $item['subtitle'] }}</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
