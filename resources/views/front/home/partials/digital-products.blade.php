@php
    $shopItems = collect(get_setting('home', 'download_products_items') ?? [])
        ->filter(fn($item) => filled($item['title'] ?? null))
        ->values();
    $isSlider = get_setting('home', 'download_products_slider', false);
    $allUrl = get_setting('home', 'download_products_all_url') ?: route('products.list');
@endphp

@if($shopItems->isNotEmpty())
    <section id="digital-products">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                    @include('icons.shop-line-duotone')
                </span>

                <h3 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('home', 'download_products_title') }}</h3>
            </div>

            @if($isSlider)
                <div class="flex items-center gap-2">
                    <button type="button" class="mwProductsNav inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 disabled:opacity-35 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 [&_svg]:h-4 [&_svg]:w-4" data-mw-products-prev aria-label="قبلی">
                        @include('icons.arrow-right-01')
                    </button>
                    <button type="button" class="mwProductsNav inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 disabled:opacity-35 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 [&_svg]:h-4 [&_svg]:w-4" data-mw-products-next aria-label="بعدی">
                        @include('icons.arrow-left-02')
                    </button>
                </div>
            @endif

            <a href="{{ $allUrl }}"
               class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
                مشاهده همه
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
                </span>
            </a>
        </div>

        {{-- حالت اسلایدر یا گرید --}}
        <div class="{{ $isSlider ? 'swiper mwHomeProductsSlider' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4' }}">
            @if($isSlider)<div class="swiper-wrapper">@endif
                @foreach($shopItems as $item)
                    <div class="{{ $isSlider ? 'swiper-slide !h-auto' : 'contents' }}">
                        <a href="{{ filled($item['link'] ?? null) ? $item['link'] : $allUrl }}"
                           class="group relative z-0 flex h-full flex-col overflow-hidden rounded-3xl border border-transparent bg-white ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1.5 hover:z-10 hover:shadow-[0_20px_44px_-14px_rgba(15,23,42,.22)] hover:ring-brand-300/70 dark:bg-slate-900/70 dark:ring-slate-800 dark:hover:shadow-[0_20px_44px_-14px_rgba(0,0,0,.55)] dark:hover:ring-brand-700/50">
                            {{-- تصویر با برق عبوری هاور --}}
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                <x-front.responsive-image
                                    :fallback="$item['image']['path'] ?? null"
                                    :alt="$item['title'] ?? ''"
                                    :title="$item['title'] ?? null"
                                    variant="medium_large"
                                    class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.06]"
                                    wrapper-class="absolute inset-0"
                                    :lazy="true"
                                    :async="true"
                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                                />

                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-slate-950/5 to-transparent"></div>

                                <span class="pointer-events-none absolute inset-0 translate-x-full bg-gradient-to-l from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-[-100%]" aria-hidden="true"></span>

                                @if(filled($item['badge_text'] ?? null))
                                    <span class="absolute top-2.5 right-2.5 rounded-xl bg-gradient-to-l from-brand-600 to-brand-500 px-2.5 py-1 text-[10.5px] font-black text-white shadow-lg shadow-brand-900/40 ring-1 ring-white/25 backdrop-blur-sm">
                                        {{ $item['badge_text'] }}
                                    </span>
                                @endif

                                @if(filled($item['price_text'] ?? null))
                                    <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-slate-950/55 px-2.5 py-1 text-[11.5px] font-black text-white backdrop-blur-md">
                                        {{ $item['price_text'] }}
                                        @if(filled($item['old_price_text'] ?? null))
                                            <s class="text-[9.5px] font-bold text-white/55">{{ $item['old_price_text'] }}</s>
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <div class="line-clamp-2 min-h-[3.25rem] text-sm font-extrabold leading-7 text-slate-900 transition group-hover:text-brand-700 dark:text-slate-100 dark:group-hover:text-brand-300">
                                    {{ $item['title'] }}
                                </div>

                                @if(filled($item['short_description'] ?? null))
                                    <p class="mt-1.5 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        {{ $item['short_description'] }}
                                    </p>
                                @endif

                                @if(filled($item['label'] ?? null))
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200/80 bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                            {{ $item['label'] }}
                                        </span>
                                    </div>
                                @endif

                                {{-- دکمهٔ دریافت: در حالت عادی محو، روی hover ظاهر می‌شود --}}
                                <div class="mt-auto pt-4">
                                    <span class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-brand-500/30 bg-brand-50/50 px-3.5 py-2 text-xs font-extrabold text-brand-700 transition duration-300 group-hover:border-brand-500 group-hover:bg-gradient-to-l group-hover:from-brand-600 group-hover:to-brand-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-brand-500/25 dark:border-brand-500/20 dark:bg-brand-500/5 dark:text-brand-300 dark:group-hover:border-brand-500 dark:group-hover:bg-gradient-to-l dark:group-hover:from-brand-600 dark:group-hover:to-brand-500 dark:group-hover:text-white [&_svg]:h-3.5 [&_svg]:w-3.5 [&_svg]:transition-transform [&_svg]:group-hover:-translate-x-0.5">
                                        {{ ($item['button_text'] ?? '') ?: 'دریافت' }}
                                        @include('icons.arrow-left-02')
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            @if($isSlider)</div>
            <div class="mt-4 flex items-center justify-center gap-2 mwHomeProductsPagination"></div>@endif
        </div>
    </section>
@endif
