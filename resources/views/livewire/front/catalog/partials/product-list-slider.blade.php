@php $items = get_setting('products', 'hero_slider'); @endphp

<div class="w-full">
    @if(count($items) > 0)
        <div class="hero-slider relative overflow-hidden rounded-2xl bg-slate-900">
            <div class="swiper js-product-hero-slider h-full">
                <div class="swiper-wrapper">
                    @foreach($items as $item)
                        @php
                            $hasImage = ! empty($item['image']['path']);
                            $hasSlideUrl = ! empty($item['url']);
                        @endphp

                        <div class="swiper-slide relative {{ $hasImage ? '' : 'min-h-[360px]' }}">
                            @if($hasImage)
                                <img class="block h-auto w-full object-cover opacity-80"
                                     src="{{ $item['image']['path'] }}"
                                     alt="{{ $item['title'] ?? '' }}">
                            @endif

                            @if($hasSlideUrl)
                                <a href="{{ $item['url'] }}"
                                   class="absolute inset-0 z-20"
                                   aria-label="{{ $item['title'] ?? 'slide link' }}"></a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="absolute top-6 left-6 z-20 hidden items-center gap-2 sm:flex">
                <button
                    class="heroPrev cursor-pointer h-10 w-10 rounded-2xl bg-white/10 text-white hover:bg-white/15 transition grid place-items-center"
                    aria-label="Previous" type="button">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M10 7l5 5-5 5" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button
                    class="heroNext cursor-pointer h-10 w-10 rounded-2xl bg-white/10 text-white hover:bg-white/15 transition grid place-items-center"
                    aria-label="Next" type="button">
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
