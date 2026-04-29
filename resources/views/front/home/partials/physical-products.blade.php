<section>
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">⬤</span>
            <h3 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('home', 'products_title') }}</h3>
        </div>
        <a href="{{ get_setting('home', 'products_all_products_url') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
            مشاهده همه
            <span class="[&_svg]:w-4 [&_svg]:h-4">
                @include('icons.arrow-left-02')
            </span>
        </a>
    </div>

    <div class="swiper physicalSwiper relative py-10 " dir="rtl">
        <div class="swiper-wrapper pb-8">
            @foreach(get_setting('home', 'products_items') as $item)
                <div class="swiper-slide max-w-[260px]">
                    <article
                        class="rounded-3xl overflow-hidden bg-slate-900 shadow-xl group transition dark:shadow-black/30">
                        <a href="{{ $item['url'] }}" class="block relative">
                            <div class="relative aspect-[3/4]">
                                <x-front.responsive-image
                                    :fallback="$item['image']['path'] ?? null"
                                    :alt="$item['title'] ?? ''"
                                    :title="$item['title'] ?? null"
                                    variant="medium_large"
                                    class="h-full w-full object-cover"
                                    wrapper-class="absolute inset-0"
                                    :lazy="true"
                                    :async="true"
                                    :fetchpriority="null"
                                    sizes="260px"
                                />

                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                                @if($item['button_title'])
                                    <div class="absolute bottom-5 left-1/2 -translate-x-1/2">
                                        <div
                                            class="rounded-2xl bg-white/90 backdrop-blur px-4 py-2 text-xs font-extrabold text-slate-900 shadow-lg dark:bg-slate-950/90 dark:text-slate-100 dark:shadow-black/20">
                                            {{ $item['button_title'] }}
                                        </div>
                                    </div>
                                @endif

                                @if($item['label'])
                                    <div
                                        class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-xl bg-white/90 px-2 py-1 text-[11px] font-bold text-slate-700 dark:bg-slate-950/90 dark:text-slate-200">
                                        {{ $item['label'] }}
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="px-4 py-4 bg-white dark:bg-slate-900">
                            <div class="text-sm font-extrabold truncate text-center dark:text-slate-100">
                                {{ $item['title'] }}
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination mt-6"></div>
    </div>
</section>
