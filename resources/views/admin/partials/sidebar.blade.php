@php $items = config('admin-sidebar', []); @endphp

<aside
    class="sidebar bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 border-l border-l-gray-50 dark:border-l-gray-800"
    id="Sidebar">
    <a href="{{ route('home') }}"
       class="flex items-center gap-[10px] pt-[8px] pb-[4px] px-[10px] mb-[4px] text-gray-700 dark:text-white transition hover:opacity-70">
        <div
            class="w-8 h-8 rounded-[12px] bg-[conic-gradient(from_160deg_at_30%_30%,theme(colors.orange.500),theme(colors.brand.500),theme(colors.brand.400),theme(colors.yellow.500))] flex items-center justify-center text-gray-700 dark:text-white font-bold text-[18px]">
            م
        </div>
        <div class="flex flex-col gap-[2px]">
            <div class="text-[0.9rem] font-bold">
                مودینگ وی
            </div>
            <div class="text-[11px] opacity-75">پنل مدیریت آموزشی</div>
        </div>
    </a>

    <nav>
        <ul class="space-y-2">
            @foreach($items as $item)
                <x-admin.sidebar.sidebar-item :item="$item"/>
            @endforeach
        </ul>
    </nav>
</aside>
