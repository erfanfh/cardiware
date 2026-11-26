{{--
    3D cards block tilt — {type: tilt-cards, title?, cards: [{num, pos:[fa,en], rating}]}
--}}
@php($locale = 'en')
<div class="reveal">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        @foreach($block['cards'] as $p)
            <div data-tilt="14" class="tilt-card group relative select-none rounded-3xl border border-edge/70 bg-gradient-to-b from-surface-2 to-surface/80 p-6 text-center">
                <div data-tilt-inner>
                    <div class="flex items-start justify-between">
                        <span class="font-num text-3xl font-black text-ink">{{ $p['num'] }}</span>
                        <span class="font-num grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-accent to-accent-2 text-lg font-black text-page shadow-[0_0_24px_-6px_var(--landing-accent)]">{{ $p['rating'] }}</span>
                    </div>
                    <span class="mx-auto mt-4 grid size-24 place-items-center rounded-full border border-edge/60 bg-page/60">
                        @include('front.landings.partials.icon', ['name' => $p['icon'] ?? 'face', 'class' => 'size-12 text-muted transition-colors group-hover:text-accent'])
                    </span>
                    <p class="mt-5 text-sm font-extrabold text-ink">{{ $p['pos'] }}</p>
                    @if(!empty($p['note']))
                        <p class="mt-1 text-[11px] text-muted">{{ $p['note'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
