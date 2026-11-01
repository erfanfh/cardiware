@php
    $specialPostsItems = collect(get_setting('home', 'special_posts_items') ?? [])
        ->filter(fn($item) => filled($item['title'] ?? null) && filled($item['image']['path'] ?? null))
        ->values();
@endphp

@if($specialPostsItems->isNotEmpty())
    <section id="special-posts">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                    @include('icons.shop-line-duotone')
                </span>

                <h3 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('home', 'special_posts_title') }}</h3>
            </div>

            @if(filled(get_setting('home', 'download_products_all_url')))
                <a href="{{ get_setting('home', 'download_products_all_url') }}"
                   class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
                    مشاهده همه
                    <span class="[&_svg]:w-4 [&_svg]:h-4">
                        @include('icons.arrow-left-02')
                    </span>
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($specialPostsItems as $item)
                <a href="{{ $item['link'] }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-500/40 hover:shadow-xl dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/40">
                    <div class="relative overflow-hidden bg-slate-100 dark:bg-slate-800">
                        <x-front.responsive-image
                            :fallback="$item['image']['path'] ?? null"
                            :alt="$item['title'] ?? ''"
                            :title="$item['title'] ?? null"
                            variant="medium_large"
                            class="w-full h-auto object-cover transition duration-500 group-hover:scale-105"
                            wrapper-class="block w-full"
                            :lazy="true"
                            :async="true"
                            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                        />

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 to-transparent"></div>

                        @if(filled($item['badge_text'] ?? null))
                            <span class="absolute top-2.5 right-2.5 rounded-xl bg-brand-500 px-2.5 py-1 text-[10.5px] font-black text-white shadow-lg shadow-brand-500/40">
                                {{ $item['badge_text'] }}
                            </span>
                        @endif
                    </div>

                    <div class="p-4">
                        <div class="text-sm font-extrabold leading-7 line-clamp-2 dark:text-slate-100">
                            {{ $item['title'] }}
                        </div>

                        @if(filled($item['short_description'] ?? null))
                            <p class="mt-1.5 text-xs leading-6 text-slate-500 line-clamp-1 dark:text-slate-400">
                                {{ $item['short_description'] }}
                            </p>
                        @endif

                        @if(filled($item['price_text'] ?? null))
                            <div class="mt-3 flex items-baseline gap-2">
                                <b class="text-sm font-black text-brand-600 dark:text-brand-400">{{ $item['price_text'] }}</b>

                                @if(filled($item['old_price_text'] ?? null))
                                    <s class="text-[11px] text-slate-400">{{ $item['old_price_text'] }}</s>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
