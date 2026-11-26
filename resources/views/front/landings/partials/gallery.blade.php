<section id="gallery" class="section-pad scroll-mt-24"
         x-data="{ active: null }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
            'subtitle' => $section['subtitle'] ?? null,
        ])

        <div class="reveal mt-14">
            <div class="swiper" data-gallery>
                <div class="swiper-wrapper">
                    @foreach($section['images'] as $image)
                        <div class="swiper-slide">
                            <button @click="active = '{{ asset($image['src']) }}'" class="group relative block w-full overflow-hidden rounded-2xl border border-edge/70" aria-label="{{ $image['caption'] }}">
                                <img src="{{ asset($image['src']) }}" alt="{{ $image['caption'] }}" class="aspect-video w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" loading="lazy">
                                <span class="absolute bottom-3 right-3 rounded-lg bg-black/60 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">{{ $image['caption'] }}</span>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination !relative !bottom-auto mt-6"></div>
            </div>
        </div>
    </div>

    {{-- Lightbox --}}
    <div x-show="active" x-cloak x-transition.opacity class="fixed inset-0 z-[60] grid place-items-center bg-black/90 p-4 backdrop-blur" @click.self="active = null" @keydown.escape.window="active = null">
        <div class="relative max-w-5xl">
            <img :src="active" alt="" class="w-full rounded-2xl border border-edge">
            <button @click="active = null" class="absolute -top-12 left-0 grid size-9 place-items-center rounded-full border border-edge bg-surface text-ink" aria-label="Close">
                @include('front.landings.partials.icon', ['name' => 'x', 'class' => 'size-4'])
            </button>
        </div>
    </div>
</section>
