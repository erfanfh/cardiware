{{--
    صفحه اختصاصی هر تیم — /{landing}/leagues/{league}/{team}
    نشان + اطلاعات (چهره‌ها، استادیوم، مربی) + گالری اسکرین‌شات + صحنه‌ها
--}}
@extends('front.landings.master')

@php($locale = $locale ?? 'fa')
@php($prefix = $locale === 'en' ? '/en' : '')

@section('body')
    @include('front.landings.partials.navbar')

    <main class="pt-16">
        {{-- هیروی تیم --}}
        <section class="relative overflow-hidden border-b border-edge/60">
            @if(!empty($team['hero_image']))
                <div class="absolute inset-0" aria-hidden="true">
                    <img src="{{ asset($team['hero_image']) }}" alt="" class="size-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-l from-page via-page/85 to-page/40"></div>
                </div>
            @endif
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-32 left-1/2 h-80 w-[52rem] -translate-x-1/2 rounded-full blur-3xl"
                     style="background: radial-gradient(closest-side, var(--landing-glow), transparent)"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 pb-12 pt-12 sm:px-6 lg:px-8">
                {{-- breadcrumb --}}
                <nav class="flex flex-wrap items-center gap-2 text-xs text-muted" aria-label="breadcrumb">
                    <a href="{{ $prefix }}/{{ $slug }}" class="transition-colors hover:text-accent">{{ $data['product_name'] }}</a>
                    <span class="opacity-50">/</span>
                    <a href="{{ $prefix }}/{{ $slug }}/leagues" class="transition-colors hover:text-accent">{{ $locale === 'en' ? 'Leagues' : 'لیگ‌ها' }}</a>
                    <span class="opacity-50">/</span>
                    <a href="{{ $prefix }}/{{ $slug }}/leagues/{{ $leagueSlug }}" class="transition-colors hover:text-accent">{{ $league['name'] }}</a>
                    <span class="opacity-50">/</span>
                    <span class="font-bold text-accent">{{ $team['name'] }}</span>
                </nav>

                <div class="mt-8 flex flex-col items-start gap-7 sm:flex-row sm:items-center">
                    <span data-tilt="9" class="tilt-card glow-frame grid size-28 shrink-0 place-items-center rounded-3xl border border-edge/80 bg-surface-2">
                        <span data-tilt-inner class="grid place-items-center">
                            @if(!empty($team['logo']))
                                <img src="{{ \App\Support\Landings::logoUrl($team['logo']) }}" alt="{{ $team['name'] }}" class="size-20 object-contain">
                            @else
                                <span class="font-num text-xl font-black tracking-widest text-muted">?</span>
                            @endif
                        </span>
                    </span>

                    <div class="min-w-0">
                        <h1 class="text-3xl font-black text-ink sm:text-4xl">{{ $team['name'] }}</h1>
                        <p class="mt-2 text-sm text-muted">
                            {{ $team['city'] }}
                            <span class="opacity-50">•</span>
                            {{ $league['name'] }}
                        </p>
                    </div>
                </div>

                {{-- بج‌های وضعیت پچ برای این تیم --}}
                <div class="mt-9 grid grid-cols-1 gap-3 sm:grid-cols-3 lg:max-w-3xl">
                    @foreach($team['stats'] as $stat)
                        <div class="flex items-center gap-3 rounded-2xl border border-edge/70 bg-surface/60 px-4 py-3.5 backdrop-blur-sm">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-accent/12 text-accent">
                                @include('front.landings.partials.icon', ['name' => ['face', 'stadium', 'mic'][$loop->index] ?? 'check', 'class' => 'size-4'])
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-extrabold text-ink">{{ $stat['value'] }}</span>
                                <span class="block text-[11px] text-muted">{{ $stat['label'] }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- توضیح + بلوک‌های محتوایی (ترتیب و ترکیب از config) --}}
        <section class="section-pad">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if(!empty($team['description']))
                    <p class="mx-auto mb-16 max-w-3xl text-center text-base leading-8 text-muted">{{ $team['description'] }}</p>
                @endif

                <div class="space-y-20">
                    @foreach($team['blocks'] as $block)
                        @includeIf('front.landings.partials.blocks.'.$block['type'], ['block' => $block])
                    @endforeach
                </div>

                {{-- تگ‌های صحنه --}}
                <div class="mt-12 flex flex-wrap items-center justify-center gap-2.5">
                    @foreach($team['tags'] as $tag)
                        <span class="rounded-full border border-edge/70 bg-surface/60 px-4 py-2 text-xs font-bold text-muted">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>

                {{-- لینک‌های پایانی --}}
                <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ $prefix }}/{{ $slug }}/leagues/{{ $leagueSlug }}" class="btn-ghost">
                        @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-4'])
                        {{ $locale === 'en' ? 'Back to '.$league['name'] : 'بازگشت به '.$league['name'] }}
                    </a>
                    <a href="{{ $prefix }}/{{ $slug }}#pricing" class="btn-primary">
                        {{ $locale === 'en' ? 'Buy the mod' : 'خرید پچ' }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    @include('front.landings.partials.footer')
@endsection
