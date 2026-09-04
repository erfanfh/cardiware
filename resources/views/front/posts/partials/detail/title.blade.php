<section class="mb-6">
    <div
        class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
               dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">

        <div
            class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px]
                   bg-brand-100/70 blur-[1px] dark:bg-brand-500/10">
        </div>

        <button
            type="button"
            x-data
            x-on:click="$dispatch('open-modal', 'post-share-{{ $post->id }}')"
            aria-label="اشتراک‌گذاری نوشته"
            title="اشتراک‌گذاری نوشته"
            class="group absolute top-4 left-4 z-10 inline-flex h-9 items-center justify-center
                   gap-2 rounded-xl border border-slate-200/80 bg-white/80 px-2.5 cursor-pointer
                   text-xs font-extrabold text-slate-600 shadow-sm backdrop-blur-md transition
                   hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700
                   focus:outline-none focus:ring-2 focus:ring-brand-500/20
                   dark:border-slate-700/80 dark:bg-slate-800/80 dark:text-slate-300
                   dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300
                   [&_svg]:h-4 [&_svg]:w-4">
            @include('icons.share-08')

            <span class="hidden sm:inline">
                اشتراک‌گذاری
            </span>
        </button>

        <nav
            class="flex flex-wrap items-center gap-1.5 pl-11 text-xs text-slate-500
                   dark:text-slate-400 [&_svg]:h-3 [&_svg]:w-3">

            <a
                href="{{ route('home') }}"
                class="font-bold transition hover:text-brand-700 dark:hover:text-brand-300">
                خانه
            </a>

            @include('icons.arrow-left-01')

            <a
                href="{{ front_route('posts.list') }}"
                class="font-bold transition hover:text-brand-700 dark:hover:text-brand-300">
                نوشته‌ها
            </a>
        </nav>

        <div class="mt-4 flex flex-col gap-4">
            <div class="flex flex-wrap gap-1.5">
                @if($features->isSpecial)
                    <div
                        class="inline-flex items-center gap-2 rounded-xl border border-brand-200
                               bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800
                               dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">

                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>

                        ویژه
                    </div>
                @endif

                @if($features->isVip)
                    <div
                        class="inline-flex items-center gap-2 rounded-xl border border-yellow-200
                               bg-yellow-50 px-2 py-1 text-[11px] font-extrabold text-yellow-800
                               dark:border-yellow-500/20 dark:bg-yellow-500/10 dark:text-yellow-300">

                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-yellow-600"></span>

                        VIP
                    </div>
                @endif

                @if($features->isUpdated)
                    <div
                        class="inline-flex items-center gap-2 rounded-xl border border-green-200
                               bg-green-50 px-2 py-1 text-[11px] font-extrabold text-green-800
                               dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">

                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-600"></span>

                        آپدیت شد
                    </div>
                @endif
            </div>

            <h1 class="text-base font-black leading-tight text-slate-900 sm:text-lg md:text-2xl dark:text-white">
                {{ $post->title }}
            </h1>

            <div
                class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-3 text-xs text-slate-500
                       dark:text-slate-400 [&_svg]:h-[14px] [&_svg]:w-[14px]">

                <div class="flex items-center gap-1">
                    @include('icons.calendar-03')

                    <span>
                        {{ jalali_date($post->published_at, 'd F Y') }}
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    @include('icons.user')

                    <span>
                        {{ $post->author->display_name }}
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    @include('icons.view')

                    <span>
                        {{ number_format(public_page_views($post)) }} بازدید
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    @include('icons.comment-01')

                    <span>
                        {{ $post->approved_comments_count }} دیدگاه
                    </span>
                </div>

                <span class="mr-auto"></span>

                <a
                    href="#send-comment-form"
                    class="inline-flex items-center gap-2 font-extrabold text-brand-700
                           transition hover:text-brand-800
                           dark:text-brand-300 dark:hover:text-brand-200">

                    رفتن به دیدگاه‌ها

                    <span class="font-black">←</span>
                </a>
            </div>
        </div>
    </div>
</section>
