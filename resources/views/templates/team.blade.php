{{--
Template Name: صفحه معرفی تیم
--}}

@extends('front.master')

@php
    $highlights = collect($teamPage['highlights_items'] ?? [])
        ->filter(fn (array $item) => filled($item['title'] ?? null))
        ->values();
    $processItems = collect($teamPage['process_items'] ?? [])
        ->filter(fn (array $item) => filled($item['title'] ?? null))
        ->values();
    $highlightIcons = ['icons.user-multiple', 'icons.puzzle', 'icons.checkmark-badge-01'];
@endphp

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-5 sm:py-8">
        <section
            class="relative isolate overflow-hidden rounded-[1.75rem] border border-slate-800 bg-slate-950 shadow-2xl shadow-slate-900/15 dark:shadow-black/30"
            aria-labelledby="team-hero-title"
        >
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(700px_circle_at_8%_5%,rgba(249,115,22,.28),transparent_55%),radial-gradient(650px_circle_at_92%_95%,rgba(14,165,233,.17),transparent_56%)]"></div>
            <div class="absolute -left-24 top-8 -z-10 h-56 w-56 rounded-full border border-white/10"></div>
            <div class="absolute -left-8 top-24 -z-10 h-28 w-28 rounded-full border border-brand-400/20"></div>

            <div class="px-5 py-6 sm:px-8 sm:py-9 lg:px-10 lg:py-12">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-white/60">
                    <a href="{{ route('home') }}" class="font-bold transition hover:text-brand-300">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-semibold text-white/90">{{ $page->title }}</span>
                </nav>

                <div class="mt-7 grid gap-8 lg:grid-cols-[minmax(0,1fr)_410px] lg:items-end">
                    <div class="max-w-3xl">
                        @if(filled($teamPage['hero_badge']))
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-2 text-xs font-extrabold text-white/90 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-brand-400 shadow-[0_0_0_5px_rgba(251,146,60,.12)]"></span>
                                {{ $teamPage['hero_badge'] }}
                            </div>
                        @endif

                        <h1 id="team-hero-title" class="mt-4 text-2xl font-black leading-[1.7] text-white sm:text-3xl lg:text-4xl">
                            {{ $teamPage['hero_title'] }}
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-slate-300 sm:text-base">
                            {{ $teamPage['hero_description'] }}
                        </p>

                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a href="#team-members" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-brand-950/20 transition hover:-translate-y-0.5 hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-300/25">
                                {{ $teamPage['hero_primary_button_title'] }}
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-down-01')</span>
                            </a>

                            <a href="#join-team" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-extrabold text-white backdrop-blur-md transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-white/10">
                                {{ $teamPage['hero_secondary_button_title'] }}
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3" aria-label="آمار مودینگ‌وی">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/60">{{ $teamPage['stats_members_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ number_format($teamStats['members']) }}</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/60">{{ $teamPage['stats_since_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $teamPage['stats_since_year'] }}</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/60">{{ $teamPage['stats_products_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ number_format($teamStats['products']) }}</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/60">{{ $teamPage['stats_content_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ number_format($teamStats['content']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($highlights->isNotEmpty())
            <section class="mt-10" aria-labelledby="team-highlights-title">
                <div class="mb-5 flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                        @include('icons.award-03')
                    </span>
                    <div>
                        <h2 id="team-highlights-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $teamPage['highlights_title'] }}</h2>
                        @if(filled($teamPage['highlights_description']))
                            <p class="mt-1.5 max-w-3xl text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $teamPage['highlights_description'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($highlights as $item)
                        <article class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-lg sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:border-brand-500/30 dark:hover:shadow-black/30">
                            <div class="pointer-events-none absolute -left-10 -top-10 h-28 w-28 rounded-full bg-brand-100/60 blur-2xl transition group-hover:bg-brand-200/70 dark:bg-brand-500/5 dark:group-hover:bg-brand-500/10"></div>
                            <div class="relative">
                                <span class="grid h-11 w-11 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                    @include($highlightIcons[$loop->index % count($highlightIcons)])
                                </span>
                                <h3 class="mt-4 text-base font-black text-slate-950 dark:text-white">{{ $item['title'] }}</h3>
                                @if(filled($item['description'] ?? null))
                                    <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="team-members" class="mt-12 scroll-mt-24" aria-labelledby="team-members-title">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-violet-100 bg-violet-50 text-violet-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300">
                        @include('icons.user-multiple')
                    </span>
                    <div>
                        <h2 id="team-members-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $teamPage['members_title'] }}</h2>
                        @if(filled($teamPage['members_description']))
                            <p class="mt-1.5 max-w-3xl text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $teamPage['members_description'] }}</p>
                        @endif
                    </div>
                </div>

                @if($teamMembers->isNotEmpty())
                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ number_format($teamMembers->count()) }} عضو
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($teamMembers as $member)
                    @php
                        $initials = mb_strtoupper(
                            mb_substr(trim((string) $member->first_name), 0, 1).
                            mb_substr(trim((string) $member->last_name), 0, 1)
                        );
                    @endphp
                    <article class="group relative flex h-full flex-col overflow-hidden rounded-[1.65rem] border border-slate-200/90 bg-white shadow-[0_8px_30px_-24px_rgba(15,23,42,.45)] ring-1 ring-transparent transition-all duration-300 ease-out hover:-translate-y-1.5 hover:border-brand-200 hover:shadow-[0_22px_50px_-26px_rgba(249,115,22,.42)] hover:ring-brand-100/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/25 dark:hover:border-brand-500/30 dark:hover:shadow-[0_22px_50px_-26px_rgba(0,0,0,.75)] dark:hover:ring-brand-500/10">
                        <span class="pointer-events-none absolute inset-x-8 top-0 z-20 h-0.5 origin-center scale-x-0 rounded-full bg-gradient-to-r from-transparent via-brand-500 to-transparent transition-transform duration-500 ease-out group-hover:scale-x-100" aria-hidden="true"></span>

                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if($member->picture)
                                <x-front.responsive-image
                                    :media="$member->picture"
                                    :fallback="$member->picture->url"
                                    :alt="$member->full_name"
                                    variant="medium"
                                    class="h-full w-full object-cover"
                                    wrapper-class="h-full w-full transition-transform duration-700 ease-out group-hover:scale-[1.045] motion-reduce:transform-none motion-reduce:transition-none"
                                    :lazy="true"
                                    :async="true"
                                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 420px"
                                />
                            @else
                                <div class="grid h-full w-full place-items-center bg-[radial-gradient(circle_at_35%_25%,rgba(255,255,255,.8),transparent_35%),linear-gradient(135deg,#ffedd5,#fff7ed)] text-5xl font-black text-brand-600 transition-transform duration-700 ease-out group-hover:scale-[1.045] motion-reduce:transform-none motion-reduce:transition-none dark:bg-[radial-gradient(circle_at_35%_25%,rgba(255,255,255,.08),transparent_35%),linear-gradient(135deg,rgba(249,115,22,.20),rgba(15,23,42,.7))] dark:text-brand-300">
                                    {{ $initials ?: '؟' }}
                                </div>
                            @endif

                            <div class="absolute inset-x-0 bottom-0 flex items-end bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent p-4 pt-16">
                                @if(filled($member->job_title))
                                    <span class="rounded-full border border-white/15 bg-slate-950/45 px-3 py-1.5 text-[11px] font-extrabold text-white backdrop-blur-md">{{ $member->job_title }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="relative flex flex-1 flex-col bg-gradient-to-b from-white to-slate-50/45 p-5 dark:from-slate-900 dark:to-slate-950/25">
                            <div class="flex items-center gap-2.5">
                                <span class="h-7 w-1 shrink-0 rounded-full bg-gradient-to-b from-brand-400 to-brand-600 shadow-[0_0_0_4px_rgba(249,115,22,.08)] transition-transform duration-300 group-hover:scale-y-110" aria-hidden="true"></span>
                                <h3 class="min-w-0 truncate text-base font-black text-slate-950 dark:text-white">{{ $member->full_name }}</h3>
                            </div>

                            @if(filled($member->bio))
                                <p class="mt-3 flex-1 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $member->bio }}</p>
                            @endif

                            @if(filled($member->website_url) || filled($member->email))
                                <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                                    @if(filled($member->website_url))
                                        <a href="{{ $member->website_url }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 dark:focus:ring-brand-500/10">
                                            <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.link-square-02')</span>
                                            وب‌سایت
                                        </a>
                                    @endif

                                    @if(filled($member->email))
                                        <a href="mailto:{{ $member->email }}" class="inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 dark:focus:ring-brand-500/10">
                                            <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.mail-ru')</span>
                                            ایمیل
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center sm:col-span-2 lg:col-span-3 xl:col-span-4 dark:border-slate-700 dark:bg-slate-900">
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white text-slate-400 ring-1 ring-slate-200 [&_svg]:h-6 [&_svg]:w-6 dark:bg-slate-950 dark:ring-slate-700">@include('icons.user-multiple')</span>
                        <p class="mt-4 text-sm font-bold leading-7 text-slate-500 dark:text-slate-400">{{ $teamPage['members_empty_title'] }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if($processItems->isNotEmpty())
            <section class="mt-12" aria-labelledby="team-process-title">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-sky-100 bg-sky-50 text-sky-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                            @include('icons.sliders-horizontal')
                        </span>
                        <div>
                            <h2 id="team-process-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $teamPage['process_title'] }}</h2>
                            @if(filled($teamPage['process_description']))
                                <p class="mt-1.5 max-w-3xl text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $teamPage['process_description'] }}</p>
                            @endif
                        </div>
                    </div>

                    <ol class="relative mt-7 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($processItems as $item)
                            <li class="relative rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5 dark:border-slate-700 dark:bg-slate-950/45">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500 text-sm font-black text-white shadow-md shadow-brand-500/20">{{ $loop->iteration }}</span>
                                    @if(!$loop->last)
                                        <span class="hidden text-slate-300 xl:block dark:text-slate-700 [&_svg]:h-5 [&_svg]:w-5" aria-hidden="true">@include('icons.arrow-left-02')</span>
                                    @endif
                                </div>
                                <h3 class="mt-4 text-sm font-black text-slate-950 dark:text-white">{{ $item['title'] }}</h3>
                                @if(filled($item['description'] ?? null))
                                    <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['description'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        <section id="join-team" class="mt-10 scroll-mt-24" aria-labelledby="join-team-title">
            <div class="relative isolate overflow-hidden rounded-[1.75rem] bg-slate-950 p-6 shadow-xl sm:p-8 lg:p-10">
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(500px_circle_at_10%_15%,rgba(249,115,22,.32),transparent_55%),radial-gradient(450px_circle_at_90%_90%,rgba(14,165,233,.17),transparent_60%)]"></div>
                <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div class="max-w-3xl">
                        @if(filled($teamPage['cta_badge']))
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3.5 py-2 text-xs font-extrabold text-brand-200 backdrop-blur">
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.waving-hand-02')</span>
                                {{ $teamPage['cta_badge'] }}
                            </span>
                        @endif
                        <h2 id="join-team-title" class="mt-4 text-xl font-black leading-9 text-white sm:text-2xl lg:text-3xl">{{ $teamPage['cta_title'] }}</h2>
                        <p class="mt-3 text-sm leading-8 text-slate-300 sm:text-base">{{ $teamPage['cta_description'] }}</p>
                    </div>

                    @if($teamPage['cta_primary_button_url'] || $teamPage['cta_secondary_button_url'])
                        <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                            @if($teamPage['cta_primary_button_url'] && filled($teamPage['cta_primary_button_title']))
                                <a href="{{ $teamPage['cta_primary_button_url'] }}" class="inline-flex min-h-12 items-center justify-center gap-2 whitespace-nowrap rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white transition hover:-translate-y-0.5 hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-300/25">
                                    {{ $teamPage['cta_primary_button_title'] }}
                                    <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                                </a>
                            @endif

                            @if($teamPage['cta_secondary_button_url'] && filled($teamPage['cta_secondary_button_title']))
                                <a href="{{ $teamPage['cta_secondary_button_url'] }}" class="inline-flex min-h-12 items-center justify-center gap-2 whitespace-nowrap rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-extrabold text-white backdrop-blur transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-white/10">
                                    {{ $teamPage['cta_secondary_button_title'] }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection
