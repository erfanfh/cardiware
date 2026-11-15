{{--
    صفحه اختصاصی هر لیگ — /{landing}/leagues/{league}
    هیرو (لوگو + پرچم کشور + آمار) + توضیح + گرید تیم‌ها با لوگو
--}}
@extends('front.landings.master')

@php($locale = $locale ?? 'fa')
@php($prefix = $locale === 'en' ? '/en' : '')

@section('body')
    @include('front.landings.partials.navbar')

    <main class="pt-16">
        {{-- هیروی لیگ --}}
        <section class="relative overflow-hidden border-b border-edge/60">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute inset-0 bg-grid opacity-30"></div>
                <div class="absolute -top-32 left-1/2 h-80 w-[52rem] -translate-x-1/2 rounded-full blur-3xl"
                     style="background: radial-gradient(closest-side, var(--landing-glow), transparent)"></div>
            </div>

            @php($logoFile = \App\Support\Landings::leagueLogo($leagueSlug, $league['logo'] ?? null))
            <div class="relative mx-auto max-w-7xl px-4 pb-12 pt-12 sm:px-6 lg:px-8">
                {{-- breadcrumb --}}
                <nav class="flex flex-wrap items-center gap-2 text-xs text-muted" aria-label="breadcrumb">
                    <a href="{{ $prefix }}/{{ $slug }}" class="transition-colors hover:text-accent">{{ $data['product_name'] }}</a>
                    <span class="opacity-50">/</span>
                    <a href="{{ $prefix }}/{{ $slug }}/leagues" class="transition-colors hover:text-accent">
                        {{ $locale === 'en' ? 'Leagues' : 'لیگ‌ها' }}
                    </a>
                    <span class="opacity-50">/</span>
                    <span class="font-bold text-accent">{{ $league['name'] }}</span>
                </nav>

                <div class="mt-8 flex flex-col items-start gap-7 sm:flex-row sm:items-center">
                    <span class="glow-frame grid size-28 shrink-0 place-items-center rounded-3xl border border-edge/80 bg-surface-2">
                        @if(!empty($logoFile))
                            <img src="{{ \App\Support\Landings::logoUrl($logoFile) }}" alt="{{ $league['name'] }}" class="size-20 object-contain">
                        @else
                            <span class="font-num text-xl font-black tracking-widest text-muted">{{ $league['tag'] }}</span>
                        @endif
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-num rounded-lg border border-accent/25 bg-accent/10 px-2.5 py-1 text-[11px] font-bold tracking-widest text-accent">{{ $league['tag'] }}</span>
                            <span class="rounded-lg bg-surface-2 px-2.5 py-1 text-[11px] font-bold text-muted">{{ $league['level'] }}</span>
                        </div>
                        <h1 class="mt-3 text-3xl font-black text-ink sm:text-4xl">{{ $league['name'] }}</h1>
                        <p class="mt-2 text-sm text-muted">
                            {{ $league['country'] }}
                            <span class="opacity-50">•</span>
                            <span class="font-num">{{ $league['teams_count'] }} {{ $locale === 'en' ? 'clubs' : 'تیم' }}</span>
                        </p>
                    </div>
                </div>

                <p class="mt-8 max-w-3xl text-base leading-8 text-muted">{{ $league['description'] }}</p>
            </div>
        </section>

        {{-- گرید تیم‌ها --}}
        <section class="section-pad">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4">
                    <h2 class="text-2xl font-black text-ink">
                        {{ $locale === 'en' ? 'Clubs' : 'تیم‌ها' }}
                        <span class="font-num mr-2 align-middle text-sm font-bold text-muted">({{ count($league['teams']) }})</span>
                    </h2>
                    <a href="{{ $prefix }}/{{ $slug }}#pricing" class="btn-primary !px-5 !py-2.5 text-xs">
                        {{ $data['buy_label'] ?? ($locale === 'en' ? 'Buy the mod' : 'خرید پچ') }}
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($league['teams'] as $i => $team)
                        <a href="{{ $prefix }}/{{ $slug }}/leagues/{{ $leagueSlug }}/{{ $teamSlugs[$i] }}" class="group flex items-center gap-3.5 rounded-2xl border border-edge/70 bg-surface/60 p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-accent/40">
                            <span class="grid size-14 shrink-0 place-items-center rounded-xl border border-edge/60 bg-surface-2 p-1.5">
                                @if(!empty($team['logo']))
                                    <img src="{{ \App\Support\Landings::logoUrl($team['logo']) }}" alt="{{ $team['name'] }}" class="size-full object-contain" loading="lazy">
                                @else
                                    <span class="font-num text-lg font-black text-muted/70">?</span>
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-extrabold text-ink group-hover:text-accent">{{ $team['name'] }}</span>
                                <span class="mt-0.5 block truncate text-[11px] text-muted">{{ $team['city'] }}</span>
                            </span>
                            @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-3.5 shrink-0 self-center text-muted opacity-0 transition-all group-hover:opacity-100 group-hover:text-accent rtl:group-hover:-translate-x-1 ltr:group-hover:translate-x-1'])
                        </a>
                    @endforeach
                </div>

                <p class="mt-8 text-center text-xs text-muted">
                    {{ $locale === 'en'
                        ? 'Club crests are imported directly from the mod\'s emblem database.'
                        : 'نشان باشگاه‌ها مستقیماً از دیتابیس نشان‌های پچ وارد شده‌اند.' }}
                </p>
            </div>
        </section>

        @include('front.landings.partials.detail.see-also', ['pageSlug' => 'leagues'])
    </main>

    @include('front.landings.partials.footer')
@endsection
