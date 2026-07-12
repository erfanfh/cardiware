<section class="mb-6">
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
        <div
            class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
        <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

        <nav class="text-xs text-slate-500 flex flex-wrap gap-2 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-700 font-bold transition dark:hover:text-brand-300">خانه</a>
            <span>—</span>
            <a href="{{ front_route('posts.list') }}"
               class="hover:text-brand-700 font-bold transition dark:hover:text-brand-300">نوشته‌ها</a>
            <span>—</span>
            <span class="text-slate-600 font-semibold dark:text-slate-300">{{ $post->title }}</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4">
            <div class="flex flex-wrap gap-1.5">
                @if($features->isSpecial)
                    <div
                        class="inline-flex items-center gap-2 text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-200 px-2 py-1 rounded-xl dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                        ویژه
                    </div>
                @endif

                @if($features->isVip)
                    <div
                        class="inline-flex items-center gap-2 text-[11px] font-extrabold text-yellow-800 bg-yellow-50 border border-yellow-200 px-2 py-1 rounded-xl dark:border-yellow-500/20 dark:bg-yellow-500/10 dark:text-yellow-300">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-yellow-600"></span>
                        VIP
                    </div>
                @endif

                @if($features->isUpdated)
                    <div
                        class="inline-flex items-center gap-2 text-[11px] font-extrabold text-green-800 bg-green-50 border border-green-200 px-2 py-1 rounded-xl dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-600"></span>
                        آپدیت شد
                    </div>
                @endif
            </div>

            <h1 class="text-xl sm:text-2xl font-black leading-tight text-slate-900 dark:text-white">
                {{ $post->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span>{{ jalali_date($post->published_at, 'd F Y') }}</span>
                <span class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
                <span>{{ $post->author->display_name }}</span>
                <span class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
                <span>{{ number_format(page_views($post)) }} بازدید</span>
                <span class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
                <span>{{ $post->approved_comments_count }} دیدگاه</span>

                <span class="mr-auto"></span>

                <a href="#send-comment-form"
                   class="inline-flex items-center gap-2 font-extrabold text-brand-700 hover:text-brand-800 transition dark:text-brand-300 dark:hover:text-brand-200">
                    رفتن به دیدگاه‌ها <span class="font-black">←</span>
                </a>
            </div>
        </div>
    </div>
</section>
