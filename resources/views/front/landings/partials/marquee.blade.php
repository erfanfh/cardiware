@php($locale = $locale ?? 'fa')
<section class="overflow-hidden py-10" dir="ltr" aria-label="leagues">
    <div class="marquee">
        <div class="marquee__track">
            {{-- آیتم‌ها دوبار تکرار می‌شوند تا حلقه بی‌درز باشد --}}
            @foreach(array_merge($section['items'], $section['items']) as $item)
                @php($logoFile = !empty($item['slug']) ? \App\Support\Landings::leagueLogo($item['slug'], $item['logo'] ?? null) : ($item['logo'] ?? null))
                <span class="league-chip {{ !empty($logoFile) ? 'league-chip--logo' : '' }}" dir="rtl">
                    @if(!empty($logoFile))
                        <img src="{{ asset('images/landings/logos/emb/'.$logoFile) }}" alt="" class="size-7 object-contain" loading="lazy">
                    @else
                        <span class="font-num text-[10px] font-bold tracking-widest text-accent">{{ $item['tag'] }}</span>
                    @endif
                    <span class="text-sm font-bold text-muted">{{ $item['label'] }}</span>
                </span>
            @endforeach
        </div>
        <div class="pointer-events-none absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-page to-transparent"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-page to-transparent"></div>
    </div>
</section>
