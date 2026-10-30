{{--
    بلوک گالری — {type: gallery, title?, images: [{src, caption}]}
--}}
@php($locale = $locale ?? 'fa')
<div class="reveal" x-data="{ active: null }">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])

    <div class="swiper" data-gallery>
        <div class="swiper-wrapper">
            @foreach($block['images'] as $img)
                <div class="swiper-slide">
                    <button @click="active = '{{ asset($img['src']) }}'" class="group relative block w-full overflow-hidden rounded-2xl border border-edge/70" aria-label="{{ $img['caption'] ?? '' }}">
                        <img src="{{ asset($img['src']) }}" alt="{{ $img['caption'] ?? '' }}" class="aspect-video w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" loading="lazy">
                        @if(!empty($img['caption']))
                            <span class="absolute bottom-3 right-3 rounded-lg bg-black/60 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">{{ $img['caption'] }}</span>
                        @endif
                    </button>
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination !relative !bottom-auto mt-6"></div>
    </div>

    <div x-show="active" x-cloak x-transition.opacity class="fixed inset-0 z-[60] grid place-items-center bg-black/90 p-4 backdrop-blur" @click.self="active = null" @keydown.escape.window="active = null">
        <div class="relative max-w-5xl">
            <img :src="active" alt="" class="w-full rounded-2xl border border-edge">
            <button @click="active = null" class="absolute -top-12 left-0 grid size-9 place-items-center rounded-full border border-edge bg-surface text-ink" aria-label="close">
                @include('front.landings.partials.icon', ['name' => 'x', 'class' => 'size-4'])
            </button>
        </div>
    </div>
</div>
