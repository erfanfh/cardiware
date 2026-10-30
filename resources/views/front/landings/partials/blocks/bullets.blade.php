{{--
    بلوک تیترهای آیکون‌دار — {type: bullets, title?, items: [{icon, title, text}]}
    آیکون‌ها: crown | star | fire | target | medal | trophy | shield | bolt | mic | face | stadium | tv | gamepad | shirt | globe | check
--}}
@php($locale = $locale ?? 'fa')
<div class="reveal">
    @include('front.landings.partials.blocks.block-head', ['block' => $block])

    <div class="mx-auto max-w-3xl space-y-4">
        @foreach($block['items'] as $item)
            <div class="group flex items-start gap-4 rounded-2xl border border-edge/60 bg-surface/50 p-5 transition-all duration-300 hover:border-accent/40 hover:bg-surface">
                <span class="relative mt-0.5 grid size-11 shrink-0 place-items-center rounded-xl border border-accent/20 bg-accent/10 text-accent">
                    @include('front.landings.partials.icon', ['name' => $item['icon'] ?? 'star', 'class' => 'size-5'])
                    @if(in_array($item['icon'] ?? '', ['fire', 'crown', 'star']))
                        <span class="pointer-events-none absolute inset-0 rounded-xl opacity-0 blur-md transition-opacity duration-300 group-hover:opacity-100" style="background: var(--landing-glow)"></span>
                    @endif
                </span>
                <div class="min-w-0">
                    <h4 class="text-base font-extrabold text-ink">{{ $item['title'] }}</h4>
                    <p class="mt-1.5 text-sm leading-7 text-muted">{{ $item['text'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
