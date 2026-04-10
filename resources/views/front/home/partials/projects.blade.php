<section>
    <div class="mx-auto max-w-7xl px-4">
        <!-- Header -->
        <div class="flex items-end justify-between gap-4 mb-4">
            <div>
                <h2 class="text-base font-extrabold">{{ get_setting('home', 'projects_title') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ get_setting('home', 'projects_subtitle') }}</p>
            </div>

            <a href="#"
               class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800 transition">
                مشاهده همه <span class="font-black" dir="ltr">←</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach(get_setting('home', 'projects_items')['projects'] ?? [] as $item)
                <article
                    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:shadow-lg transition">

                    <div
                        class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                    <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                    <div class="flex items-center gap-4">
                        <div class="shrink-0">
                            <div class="h-12 w-12 rounded-2xl border border-slate-200 bg-white grid place-items-center">
                                <span class="text-xs font-extrabold text-slate-400">SVG</span>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm sm:text-base font-extrabold truncate">{{ $item->title }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $item->excerpt }}</div>
                                </div>


                                <span
                                    class="shrink-0 inline-flex items-center gap-2 rounded-2xl bg-brand-50 border border-brand-100 px-3 py-1.5 text-[11px] font-extrabold text-brand-800">
                                    <span
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-white text-[11px] font-black">%</span>
                                    {{ $item->progress_percent }}٪ تکمیل
                                  </span>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>پیشرفت</span>
                                    <span class="font-extrabold text-slate-700">{{ $item->progress_percent }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-brand-500"
                                         style="width: {{ $item->progress_percent . '%' }}"></div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-3">
                                <a href="{{ route('projects.detail', $item) }}"
                                   class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                    اطلاعات پروژه
                                    <span class="font-black" dir="ltr">←</span>
                                </a>

                                <span class="text-xs text-slate-500 hidden sm:inline">
                                    بروزرسانی: {{ jalali_date($item->last_updated_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="hidden sm:block shrink-0">
                            <div
                                class="h-14 w-14 rounded-3xl border border-slate-200 bg-slate-50 grid place-items-center">
                                <span class="text-xs font-extrabold text-slate-400">SVG</span>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- Mobile "View all" -->
        <div class="sm:hidden mt-4">
            <a href="#"
               class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800 transition">
                مشاهده همه <span class="font-black" dir="ltr">←</span>
            </a>
        </div>
    </div>
</section>
