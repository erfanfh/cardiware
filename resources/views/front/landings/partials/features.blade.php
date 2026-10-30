@php($locale = $locale ?? 'fa')
@php($prefix = $locale === 'en' ? '/en' : '')
<section id="features" class="section-pad scroll-mt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
            'subtitle' => $section['subtitle'] ?? null,
        ])

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($section['items'] as $item)
                @php($cardTag = !empty($item['page']) ? 'a' : 'article')
                <{{ $cardTag }}
                    @if(!empty($item['page'])) href="{{ $prefix }}/{{ $slug }}/{{ $item['page'] }}" @endif
                    class="reveal card-feature group relative overflow-hidden rounded-2xl border border-edge/70 bg-surface/60 p-6 transition-all duration-300 hover:-translate-y-1 hover:border-accent/40 hover:bg-surface">

                    <div class="pointer-events-none absolute -left-16 -top-16 size-40 rounded-full opacity-0 blur-3xl transition-opacity duration-500 group-hover:opacity-100"
                         style="background: var(--landing-glow)"></div>

                    <div class="flex items-start justify-between gap-4">
                        <span class="grid size-12 place-items-center rounded-xl border border-accent/20 bg-accent/10 text-accent">
                            @include('front.landings.partials.icon', ['name' => $item['icon'], 'class' => 'size-6'])
                        </span>
                        <span class="font-num text-2xl font-black text-accent/90">{{ $item['stat'] }}</span>
                    </div>

                    <h3 class="mt-5 inline-flex items-center gap-2 text-lg font-extrabold text-ink">
                        {{ $item['title'] }}
                        @if(!empty($item['page']))
                            @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-3.5 text-accent/70 transition-transform group-hover:-translate-x-1 rtl:group-hover:-translate-x-1 ltr:group-hover:translate-x-1'])
                        @endif
                    </h3>
                    <p class="mt-2 text-sm leading-7 text-muted">{{ $item['desc'] }}</p>
                </{{ $cardTag }}>
            @endforeach
        </div>
    </div>
</section>
