<section id="digital-products">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">⬤</span>
            <h3 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('home', 'download_products_title') }}</h3>
        </div>

        <a href="{{ get_setting('home', 'download_products_all_url') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
            مشاهده همه
            <span class="[&_svg]:w-4 [&_svg]:h-4">
                @include('icons.arrow-left-02')
            </span>
        </a>
    </div>

    <div
        class="relative rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
        <div
            class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
        <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

        <div class="p-4">
            <div class="swiper js-download-products-swiper">
                <div class="swiper-wrapper">
                    @foreach(get_setting('home', 'download_products_items') as $item)
                        <article class="swiper-slide h-auto">
                            <a href="{{ $item['link'] }}"
                               class="block h-full rounded-2xl border border-slate-200 bg-white overflow-hidden hover:shadow-lg transition dark:border-slate-700 dark:bg-slate-950 dark:hover:shadow-black/20">
                                <div class="relative aspect-[16/9] bg-slate-100 dark:bg-slate-800">
                                    <img
                                        class="absolute inset-0 h-full w-full object-cover"
                                        src="{{ $item['image']['path'] }}"
                                        alt="{{ $item['title'] }}"
                                        title="{{ $item['title'] }}"
                                    />

                                    <div
                                        class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-white/90 px-3 py-1 text-xs font-extrabold text-slate-900 ring-1 ring-slate-200 dark:bg-slate-950/85 dark:text-slate-100 dark:ring-slate-700">
                                        <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                                        پیشرفت آپدیت: {{ $item['progress_percent'] }}%
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-xl bg-brand-50 border border-brand-100 px-2 py-1 text-[11px] font-bold text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                            <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                                            {{ $item['label'] }}
                                        </span>
                                    </div>

                                    <h4 class="mt-3 font-extrabold leading-7 dark:text-slate-100">
                                        {{ $item['title'] }}
                                    </h4>

                                    <p class="mt-2 text-sm text-slate-600 leading-7 dark:text-slate-300">
                                        {{ $item['short_description'] }}
                                    </p>

                                    @if($item['enable_progress_percent'])
                                        <div class="mt-3">
                                            <div
                                                class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                                <span>پیشرفت ساخت/آپدیت</span>
                                                <span class="font-extrabold text-slate-700 dark:text-slate-200">
                                                    {{ $item['progress_percent'] }}%
                                                </span>
                                            </div>

                                            <div
                                                class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                                                <div
                                                    class="h-full bg-brand-500"
                                                    style="width: {{ $item['progress_percent'] }}%"
                                                ></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-center">
                    <div class="swiper-pagination !static !w-auto"></div>
                </div>
            </div>
        </div>
    </div>
</section>
