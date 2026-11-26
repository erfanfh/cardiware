@php($locale = 'en')
@php($prefix = $locale === 'en' ? '/en' : '')
<div>
    <h2 class="text-2xl font-black text-ink">{{ $locale === 'en' ? 'Leagues & Cups' : 'Leagues and cups' }}</h2>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach($pageData['catalog'] as $leagueSlug => $league)
            @php($logoFile = \App\Support\Landings::leagueLogo($leagueSlug, $league['logo'] ?? null))
            <a href="{{ $prefix }}/{{ $slug }}/leagues/{{ $leagueSlug }}"
               class="group flex items-center gap-4 rounded-2xl border border-edge/70 bg-surface/60 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:border-accent/40">

                <span class="grid size-16 shrink-0 place-items-center rounded-2xl border border-edge/60 bg-surface-2">
                    @if(!empty($logoFile))
                        <img src="{{ \App\Support\Landings::logoUrl($logoFile) }}" alt="{{ $league['name'] }}" class="size-12 object-contain" loading="lazy">
                    @else
                        <span class="font-num text-sm font-black tracking-widest text-muted">{{ $league['tag'] }}</span>
                    @endif
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block truncate text-base font-extrabold text-ink transition-colors group-hover:text-accent">{{ $league['name'] }}</span>
                    <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                        <span>{{ $league['country'] }}</span>
                        <span class="opacity-50">•</span>
                        <span class="font-num">{{ $league['teams_count'] }} {{ $locale === 'en' ? 'clubs' : 'Team' }}</span>
                        <span class="opacity-50">•</span>
                        <span>{{ $league['level'] }}</span>
                    </span>
                </span>

                @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-4 shrink-0 text-muted transition-all group-hover:-translate-x-1 group-hover:text-accent rtl:group-hover:-translate-x-1 ltr:group-hover:translate-x-0 ltr:group-hover:translate-x-1'])
            </a>
        @endforeach
    </div>
</div>
