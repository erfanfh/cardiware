@php $items = get_setting('home', 'hero_slider'); @endphp

<div class="w-full">
    @if(count($items) > 0)
        <div class="hero-slider relative overflow-hidden rounded-2xl bg-slate-900">
            <div class="swiper heroSwiper h-full">
                <div class="swiper-wrapper">
                    @foreach($items as $item)
                        @php
                            $hasImage = ! empty($item['image']['path']);
                            $showDarkOverlay = $item['show_dark_overlay'] ?? false;
                            $hasSlideUrl = ! empty($item['url']);
                        @endphp

                        <div class="swiper-slide relative {{ $hasImage ? '' : 'min-h-[360px]' }}">
                            @if($hasImage)
                                <img class="block h-auto w-full object-cover opacity-80"
                                     src="{{ $item['image']['path'] }}"
                                     alt="{{ $item['title'] ?? '' }}">
                            @endif

                            @if($showDarkOverlay)
                                <div
                                    class="absolute inset-0 bg-gradient-to-l from-slate-950/70 via-slate-950/40 to-slate-950/10"></div>
                            @endif

                            @if($hasSlideUrl)
                                <a href="{{ $item['url'] }}"
                                   class="absolute inset-0 z-20"
                                   aria-label="{{ $item['title'] ?? 'slide link' }}"></a>
                            @endif

                            <div class="pointer-events-none absolute inset-0 z-30 p-6 sm:p-8">
                                <div class="flex h-full flex-col">
                                    <div class="flex items-center justify-between gap-3">
                                        @if($item['top_label_text'])
                                            <div
                                                class="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-3 py-2 text-white/90 backdrop-blur">
                                                <span class="h-2 w-2 rounded-full bg-brand-400"></span>
                                                <span class="text-xs font-semibold">{{ $item['top_label_text'] }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-8 flex h-full flex-col">
                                        @if(! empty($item['square_label_text']) || ! empty($item['subtitle']) || ! empty($item['title']))
                                            <div class="flex items-center gap-3">
                                                @if($item['square_label_text'])
                                                    <div
                                                        class="h-12 w-12 rounded-2xl bg-white grid place-items-center dark:bg-slate-950">
                                                        <span class="font-black text-slate-900 dark:text-slate-100">
                                                            {{ $item['square_label_text'] }}
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="text-white">
                                                    @if($item['subtitle'])
                                                        <div
                                                            class="text-sm font-semibold text-white/80">{{ $item['subtitle'] }}</div>
                                                    @endif

                                                    @if($item['title'])
                                                        <h1 class="text-2xl sm:text-3xl font-black leading-tight mt-1">{{ $item['title'] }}</h1>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if($item['description'])
                                            <p class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                                                {!! nl2br(htmlspecialchars(e($item['description']))) !!}
                                            </p>
                                        @endif

                                        <div class="mt-auto grid grid-cols-2 gap-3 pt-6 pb-3 lg:flex lg:flex-wrap">
                                            @if($item['primary_button_text'])
                                                <a href="{{ $item['primary_button_url'] ?: '#' }}"
                                                   class="relative z-40 pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white
                                                    hover:bg-brand-600 active:bg-brand-700 transition shadow-lg dark:hover:bg-brand-400">
                                                    {{ $item['primary_button_text'] }}
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                        <path d="M12 3v10m0 0 4-4m-4 4-4-4" stroke="currentColor"
                                                              stroke-width="2"
                                                              stroke-linecap="round"/>
                                                        <path d="M5 14v5h14v-5" stroke="currentColor" stroke-width="2"
                                                              stroke-linecap="round"/>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($item['secondary_button_text'])
                                                <a href="{{ $item['secondary_button_url'] ?: '#' }}"
                                                   class="relative z-40 pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-5 py-3 text-sm font-bold text-white
                                                    hover:bg-white/15 transition backdrop-blur border border-white/10">
                                                    {{ $item['secondary_button_text'] }}
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                        <path d="M12 20h9" stroke="currentColor" stroke-width="2"
                                                              stroke-linecap="round"/>
                                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"
                                                              stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="absolute top-6 left-6 z-20 hidden items-center gap-2 sm:flex">
                <button
                    class="heroPrev cursor-pointer h-10 w-10 rounded-2xl bg-white/10 text-white hover:bg-white/15 transition grid place-items-center"
                    aria-label="قبلی" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M10 7l5 5-5 5" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button
                    class="heroNext cursor-pointer h-10 w-10 rounded-2xl bg-white/10 text-white hover:bg-white/15 transition grid place-items-center"
                    aria-label="بعدی" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 heroDots"></div>
        </div>
    @endif
</div>
