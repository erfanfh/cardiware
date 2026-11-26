@php($locale = 'en')
@php($prefix = $locale === 'en' ? '/en' : '')
<section id="leagues" class="section-pad scroll-mt-24 bg-surface/30">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
            'subtitle' => $section['subtitle'] ?? null,
        ])

        <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($section['items'] as $item)
                @php($entry = \App\Support\Landings::resolve(\App\Support\Landings::catalog('leagues.'.$item['slug']), $locale))
                @php($logoFile = \App\Support\Landings::leagueLogo($item['slug'], $item['logo'] ?: null))
                <a href="{{ $prefix }}/{{ $slug }}/leagues/{{ $item['slug'] }}"
                   class="reveal group rounded-2xl border border-edge/70 bg-surface/70 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-accent/40">

                    <div class="flex items-center justify-between">
                        <span class="grid size-12 place-items-center rounded-xl border border-edge/60 bg-surface-2">
                            @if(!empty($logoFile))
                                <img src="{{ \App\Support\Landings::logoUrl($logoFile) }}" alt="{{ $entry['name'] ?? '' }}" class="size-9 object-contain" loading="lazy">
                            @else
                                <span class="font-num text-xs font-black tracking-widest text-muted">{{ $item['tag'] }}</span>
                            @endif
                        </span>
                        <span class="rounded-lg bg-surface-2 px-2.5 py-1 text-[11px] font-bold text-muted">{{ $item['level'] }}</span>
                    </div>

                    <h3 class="mt-4 flex items-center gap-1.5 text-base font-extrabold text-ink transition-colors group-hover:text-accent">
                        <span class="truncate">{{ $entry['name'] ?? $item['tag'] }}</span>
                        @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-3.5 shrink-0 opacity-0 transition-all group-hover:opacity-100 group-hover:-translate-x-1 rtl:group-hover:-translate-x-1 ltr:group-hover:translate-x-1'])
                    </h3>

                    <div class="mt-3 flex items-center justify-between border-t border-edge/50 pt-3 text-xs text-muted">
                        <span class="inline-flex items-center gap-1.5">
                            @include('front.landings.partials.icon', ['name' => 'globe', 'class' => 'size-3.5'])
                            {{ $entry['country'] ?? '' }}
                        </span>
                        <span class="font-num">{{ $item['teams'] }} {{ $locale === 'en' ? 'clubs' : 'Team' }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="reveal mt-10 text-center">
            <a href="{{ $prefix }}/{{ $slug }}/leagues" class="btn-ghost !px-6 !py-3 text-sm">
                {{ $locale === 'en' ? 'Browse all leagues' : 'View all leagues' }}
                @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-4'])
            </a>
        </div>
    </div>
</section>
