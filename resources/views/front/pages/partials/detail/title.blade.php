<section class="mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">
        <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
        <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

        <nav class="text-xs text-slate-500 flex flex-wrap gap-2">
            <a href="{{ route('home') }}" class="hover:text-brand-700 font-bold transition">خانه</a>
            <span>—</span>
            <a href="{{ route('posts.list') }}" class="hover:text-brand-700 font-bold transition">نوشته‌ها</a>
            <span>—</span>
            <span class="text-slate-600 font-semibold">{{ $page->title }}</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4">
            <h1 class="text-xl sm:text-2xl font-black leading-tight">
                {{ $page->title }}
            </h1>

            @php
                $views = approximate_view(page_views($page));
            @endphp
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span>{{ jalali_date($page->published_at, 'd F Y') }}</span>
                <span class="h-1 w-1 rounded bg-slate-300"></span>
                <span>{{ $page->author->display_name }}</span>
                @if($views)
                    <span class="h-1 w-1 rounded bg-slate-300"></span>
                    <span>{{ $views }} بازدید</span>
                @endif
                <span class="h-1 w-1 rounded bg-slate-300"></span>
                <span>{{ $page->approved_comments_count }} دیدگاه</span>
            </div>
        </div>
    </div>
</section>
