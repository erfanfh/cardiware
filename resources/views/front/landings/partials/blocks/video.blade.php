{{--
    بلوک ویدیو — {type: video, title?, url?, poster?, caption?}
    url خالی = جای‌گذار (پوستر + دکمه پخش به‌زودی)؛ url یوتیوب/APV = امبد
--}}
@php($locale = $locale ?? 'fa')
<div class="reveal" x-data="{ playing: false }">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])

    <div class="mx-auto max-w-4xl">
        @if(!empty($block['url']))
            <div class="aspect-video overflow-hidden rounded-2xl border border-edge/70">
                @if($playing)
                    <iframe src="{{ $block['url'] }}?autoplay=1" class="size-full" title="{{ $block['title'] ?? 'video' }}" allow="autoplay; fullscreen" allowfullscreen></iframe>
                @else
                    <button @click="playing = true" class="group relative block size-full">
                        @if(!empty($block['poster']))
                            <img src="{{ asset($block['poster']) }}" alt="" class="size-full object-cover">
                        @else
                            <span class="block size-full bg-surface-2"></span>
                        @endif
                        <span class="absolute inset-0 grid place-items-center bg-black/30 group-hover:bg-black/45">
                            <span class="grid size-16 place-items-center rounded-full bg-accent/90 text-page transition-transform group-hover:scale-110">
                                @include('front.landings.partials.icon', ['name' => 'play', 'class' => 'size-7'])
                            </span>
                        </span>
                    </button>
                @endif
            </div>
        @else
            <div class="relative grid aspect-video place-items-center overflow-hidden rounded-2xl border border-edge/70 bg-surface-2">
                @if(!empty($block['poster']))
                    <img src="{{ asset($block['poster']) }}" alt="" class="absolute inset-0 size-full object-cover opacity-40">
                @endif
                <div class="relative text-center">
                    <span class="mx-auto grid size-16 place-items-center rounded-full bg-accent/15 text-accent">
                        @include('front.landings.partials.icon', ['name' => 'play', 'class' => 'size-7'])
                    </span>
                    <p class="mt-4 font-bold text-ink">{{ $block['caption'] ?? ($locale === 'en' ? 'Trailer coming soon' : 'ویدیو به‌زودی') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
