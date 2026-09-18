{{--
Template Name: صفحه درباره ما
--}}

@extends('front.master')

@php
    $heroImagePath = $aboutUs['hero_image']?->path ?: $page->picture?->url;
    $whatWeBuildItems = collect($aboutUs['what_we_build_items'] ?? [])
        ->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['subtitle'] ?? null))
        ->values();
    $faqItems = collect($aboutUs['faqs_items'] ?? [])
        ->filter(fn ($item) => filled($item['title'] ?? null) && filled($item['answer'] ?? null))
        ->values();
@endphp

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-5 sm:py-8">
        <section
            class="relative isolate overflow-hidden rounded-[1.75rem] border border-slate-800 bg-slate-950 shadow-2xl shadow-slate-900/15 dark:border-slate-800 dark:shadow-black/30"
            aria-labelledby="about-hero-title"
        >
            @if($heroImagePath)
                <x-front.responsive-image
                    :fallback="$heroImagePath"
                    alt=""
                    variant="large"
                    class="h-full w-full object-cover"
                    wrapper-class="absolute inset-0 -z-20"
                    :lazy="false"
                    :async="true"
                    sizes="(max-width: 1280px) 100vw, 1280px"
                />
            @endif

            <div
                class="absolute inset-0 -z-10 bg-[radial-gradient(700px_circle_at_15%_15%,rgba(249,115,22,.38),transparent_55%),radial-gradient(650px_circle_at_85%_80%,rgba(14,165,233,.18),transparent_58%)]"></div>
            <div class="absolute inset-0 -z-10 bg-gradient-to-l from-slate-950/95 via-slate-950/85 to-slate-950/55"></div>

            <div class="relative px-5 py-6 sm:px-8 sm:py-9 lg:px-10 lg:py-12">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-white/65">
                    <a href="{{ route('home') }}" class="font-bold transition hover:text-brand-300">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-semibold text-white/90">{{ $page->title }}</span>
                </nav>

                <div class="mt-7 grid gap-8 lg:grid-cols-[minmax(0,1fr)_400px] lg:items-end">
                    <div class="max-w-3xl">
                        @if(filled($aboutUs['hero_badge']))
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-2 text-xs font-extrabold text-white/90 backdrop-blur-md">
                                <span class="h-2 w-2 rounded-full bg-brand-400 shadow-[0_0_0_5px_rgba(251,146,60,.12)]"></span>
                                {{ $aboutUs['hero_badge'] }}
                            </div>
                        @endif

                        <h1 id="about-hero-title" class="mt-4 text-2xl font-black leading-[1.7] text-white sm:text-3xl lg:text-4xl">
                            {{ $aboutUs['hero_title'] }}
                        </h1>

                        <div class="mt-4 max-w-2xl text-sm leading-8 text-slate-200 sm:text-base [&_a]:font-bold [&_a]:text-brand-300 [&_a]:underline-offset-4 hover:[&_a]:underline">
                            {!! $aboutUs['hero_description'] !!}
                        </div>

                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="#projects"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-brand-950/20 transition hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-300/25"
                            >
                                {{ $aboutUs['hero_primary_button_title'] }}
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                            </a>

                            <a
                                href="#contact"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-extrabold text-white backdrop-blur-md transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-white/10"
                            >
                                {{ $aboutUs['hero_secondary_button_title'] }}
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.bubble-chat')</span>
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3" aria-label="آمار مودینگ‌وی">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/65">{{ $aboutUs['stats_in_progress_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ number_format($aboutUsStats['in_progress_projects']) }}</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/65">{{ $aboutUs['stats_completed_title'] }}</div>
                            <div class="mt-2 flex items-end justify-between gap-2">
                                <div class="text-2xl font-black text-white">
                                    {{ number_format($aboutUsStats['completed_projects']) }}
                                    <span class="text-xs font-bold text-white/55">از {{ number_format($aboutUsStats['total_projects']) }}</span>
                                </div>
                                <span class="text-[10px] font-black text-brand-300">{{ $aboutUsStats['completion_percentage'] }}٪</span>
                            </div>
                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/15" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $aboutUsStats['completion_percentage'] }}">
                                <div class="h-full rounded-full bg-gradient-to-l from-brand-300 to-brand-500" style="width: {{ $aboutUsStats['completion_percentage'] }}%"></div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/65">{{ $aboutUs['stats_products_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ number_format($aboutUsStats['active_products']) }}</div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md sm:p-5">
                            <div class="text-[11px] font-extrabold leading-5 text-white/65">{{ $aboutUs['stats_users_title'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ number_format($aboutUsStats['active_users']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-12" aria-labelledby="mission-title">
            <article
                class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-7 lg:col-span-7 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                <div class="pointer-events-none absolute -left-14 -top-14 h-40 w-40 rounded-full bg-brand-100/70 blur-2xl dark:bg-brand-500/10"></div>
                <div class="relative">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                            @include('icons.award-03')
                        </span>
                        <h2 id="mission-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $aboutUs['our_mission_box_title'] }}</h2>
                    </div>

                    <div class="mt-5 text-sm leading-8 text-slate-600 sm:text-base dark:text-slate-300 [&_a]:font-bold [&_a]:text-brand-600 dark:[&_a]:text-brand-300 [&_p+p]:mt-3">
                        {!! $aboutUs['our_mission_description'] !!}
                    </div>
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-7 lg:col-span-5 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-sky-100 bg-sky-50 text-sky-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                        @include('icons.puzzle')
                    </span>
                    <h2 class="text-lg font-black text-slate-950 dark:text-white">{{ $aboutUs['what_we_build_box_title'] }}</h2>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    @foreach($whatWeBuildItems as $item)
                        @php
                            $itemUrl = trim((string) ($item['url'] ?? ''));
                        @endphp
                        <div class="group relative rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:-translate-y-0.5 hover:border-brand-200 hover:bg-brand-50/60 dark:border-slate-700 dark:bg-slate-950/45 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/5">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-xl bg-white text-xs font-black text-brand-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-brand-300 dark:ring-slate-700">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">{{ $item['title'] ?? '' }}</h3>
                                    @if(filled($item['subtitle'] ?? null))
                                        <p class="mt-1.5 text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $item['subtitle'] }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($itemUrl !== '')
                                <a href="{{ $itemUrl }}" class="absolute inset-0 rounded-2xl focus:outline-none focus:ring-4 focus:ring-brand-200/60 dark:focus:ring-brand-500/20">
                                    <span class="sr-only">مشاهده {{ $item['title'] ?? '' }}</span>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section id="projects" class="mt-10 scroll-mt-24" aria-labelledby="projects-title">
            <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                        @include('icons.dashboard-square-02')
                    </span>
                    <div>
                        <h2 id="projects-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $aboutUs['projects_box_title'] }}</h2>
                        @if(filled($aboutUs['projects_box_description']))
                            <p class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $aboutUs['projects_box_description'] }}</p>
                        @endif
                    </div>
                </div>

                <a href="{{ front_route('projects.list') }}" class="inline-flex items-center gap-2 self-start text-sm font-extrabold text-brand-700 transition hover:text-brand-800 sm:self-auto dark:text-brand-300 dark:hover:text-brand-200">
                    {{ $aboutUs['projects_button_title'] }}
                    <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($aboutUsProjects as $project)
                    <x-front.project.card :project="$project"/>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center sm:col-span-2 lg:col-span-3 dark:border-slate-700 dark:bg-slate-900">
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white text-slate-400 ring-1 ring-slate-200 [&_svg]:h-5 [&_svg]:w-5 dark:bg-slate-950 dark:ring-slate-700">@include('icons.inbox')</span>
                        <p class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">در حال حاضر پروژه‌ای برای نمایش وجود ندارد.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-10" aria-labelledby="team-title">
            <div class="mb-5 flex items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-violet-100 bg-violet-50 text-violet-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300">
                    @include('icons.user-multiple')
                </span>
                <div>
                    <h2 id="team-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $aboutUs['our_team_box_title'] }}</h2>
                    @if(filled($aboutUs['our_team_description']))
                        <p class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $aboutUs['our_team_description'] }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($aboutUsTeamMembers as $member)
                    @php
                        $initials = mb_strtoupper(
                            mb_substr(trim((string) $member->first_name), 0, 1).
                            mb_substr(trim((string) $member->last_name), 0, 1)
                        );
                    @endphp
                    <article class="group flex h-full flex-col rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30">
                        <div class="flex items-center gap-4">
                            @if($member->picture)
                                <x-front.responsive-image
                                    :media="$member->picture"
                                    :fallback="$member->picture->url"
                                    :alt="$member->full_name"
                                    variant="thumbnail"
                                    class="h-16 w-16 rounded-2xl object-cover"
                                    wrapper-class="h-16 w-16 shrink-0 rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700"
                                    :lazy="true"
                                    :async="true"
                                    sizes="64px"
                                />
                            @else
                                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-100 to-orange-50 text-lg font-black text-brand-700 ring-1 ring-brand-200 dark:from-brand-500/20 dark:to-orange-500/10 dark:text-brand-200 dark:ring-brand-500/20">
                                    {{ $initials ?: '؟' }}
                                </div>
                            @endif

                            <div class="min-w-0">
                                <h3 class="truncate text-base font-black text-slate-950 dark:text-white">{{ $member->full_name }}</h3>
                                @if(filled($member->job_title))
                                    <p class="mt-1 truncate text-xs font-bold text-brand-600 dark:text-brand-300">{{ $member->job_title }}</p>
                                @endif
                            </div>
                        </div>

                        @if(filled($member->bio))
                            <p class="mt-4 flex-1 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $member->bio }}</p>
                        @endif

                        @if(filled($member->website_url) || filled($member->email))
                            <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                                @if(filled($member->website_url))
                                    <a href="{{ $member->website_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                                        <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.link-square-02')</span>
                                        وب‌سایت
                                    </a>
                                @endif

                                @if(filled($member->email))
                                    <a href="mailto:{{ $member->email }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                                        <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.mail-ru')</span>
                                        ایمیل
                                    </a>
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 p-8 text-center sm:col-span-2 lg:col-span-3 dark:border-slate-700 dark:bg-slate-900">
                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white text-slate-400 ring-1 ring-slate-200 [&_svg]:h-5 [&_svg]:w-5 dark:bg-slate-950 dark:ring-slate-700">@include('icons.user-multiple')</span>
                        <p class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">اطلاعات اعضای تیم هنوز ثبت نشده است.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-10" aria-labelledby="faq-title" x-data="{ open: {{ $faqItems->isNotEmpty() ? 0 : -1 }} }">
            <div class="mb-5 flex items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                    @include('icons.help-circle')
                </span>
                <div>
                    <h2 id="faq-title" class="text-lg font-black text-slate-950 dark:text-white">{{ $aboutUs['faqs_box_title'] }}</h2>
                    @if(filled($aboutUs['faqs_description']))
                        <p class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $aboutUs['faqs_description'] }}</p>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                @forelse($faqItems as $item)
                    @php
                        $faqIndex = $loop->index;
                    @endphp
                    <div @class(['border-t border-slate-200 dark:border-slate-800' => !$loop->first])>
                        <h3>
                            <button
                                type="button"
                                @click="open = open === {{ $faqIndex }} ? -1 : {{ $faqIndex }}"
                                :aria-expanded="open === {{ $faqIndex }}"
                                aria-controls="about-faq-{{ $faqIndex }}"
                                class="flex w-full cursor-pointer items-center justify-between gap-4 p-5 text-right transition hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-brand-100 sm:p-6 dark:hover:bg-slate-800/70 dark:focus-visible:ring-brand-500/10"
                            >
                                <span class="text-sm font-extrabold leading-7 text-slate-900 sm:text-base dark:text-slate-100">{{ $item['title'] }}</span>
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-lg font-black text-brand-600 dark:border-slate-700 dark:bg-slate-950 dark:text-brand-300" x-text="open === {{ $faqIndex }} ? '−' : '+'"></span>
                            </button>
                        </h3>
                        <div
                            id="about-faq-{{ $faqIndex }}"
                            x-show="open === {{ $faqIndex }}"
                            x-collapse
                            @if(!$loop->first) x-cloak @endif
                        >
                            <div class="px-5 pb-5 text-sm leading-8 text-slate-600 sm:px-6 sm:pb-6 dark:text-slate-300">
                                {!! nl2br(e($item['answer'])) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm font-bold text-slate-500 dark:text-slate-400">هنوز پرسشی برای این بخش ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section id="contact" class="mt-10 scroll-mt-24" aria-labelledby="contact-title">
            <div class="grid gap-6 lg:grid-cols-12">
                <article class="relative isolate overflow-hidden rounded-[1.75rem] bg-slate-950 p-6 shadow-xl sm:p-8 lg:col-span-7">
                    <div class="absolute inset-0 -z-10 bg-[radial-gradient(500px_circle_at_15%_20%,rgba(249,115,22,.32),transparent_55%),radial-gradient(450px_circle_at_90%_90%,rgba(14,165,233,.16),transparent_60%)]"></div>
                    <span class="grid h-12 w-12 place-items-center rounded-2xl border border-white/10 bg-white/10 text-brand-300 backdrop-blur [&_svg]:h-6 [&_svg]:w-6">
                        @include('icons.customer-support')
                    </span>
                    <h2 id="contact-title" class="mt-5 text-xl font-black leading-9 text-white sm:text-2xl">{{ $aboutUs['contact_us_box_title'] }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-8 text-slate-300">{{ $aboutUs['contact_us_description'] }}</p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('account.tickets') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-300/25">
                            {{ $aboutUs['contact_us_button_title'] }}
                            <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                        </a>
                        @guest
                            <span class="text-xs leading-6 text-white/55">برای ثبت و پیگیری درخواست، وارد حساب کاربری شوید.</span>
                        @endguest
                    </div>
                </article>

                <aside class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-7 lg:col-span-5 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20" aria-label="راه‌های ارتباطی">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-5 [&_svg]:w-5 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                            @include('icons.telephone')
                        </span>
                        <h2 class="text-base font-black text-slate-950 dark:text-white">{{ $aboutUs['contact_box_title'] }}</h2>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse($aboutUsContacts as $contact)
                            @php
                                $contactIcon = match ($contact['key']) {
                                    'telegram' => 'icons.telegram',
                                    'instagram' => 'icons.instagram',
                                    'email' => 'icons.mail-ru',
                                    'phone' => 'icons.telephone',
                                    default => 'icons.calendar-03',
                                };
                            @endphp
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-700 dark:bg-slate-950/45">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-brand-600 ring-1 ring-slate-200 [&_svg]:h-4 [&_svg]:w-4 dark:bg-slate-800 dark:text-brand-300 dark:ring-slate-700">
                                    @include($contactIcon)
                                </span>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500">{{ $contact['label'] }}</div>
                                    @if($contact['url'])
                                        <a
                                            href="{{ $contact['url'] }}"
                                            @if($contact['external']) target="_blank" rel="noopener" @endif
                                            class="mt-0.5 block truncate text-sm font-extrabold text-slate-800 transition hover:text-brand-600 dark:text-slate-200 dark:hover:text-brand-300"
                                            dir="auto"
                                        >{{ $contact['value'] }}</a>
                                    @else
                                        <div class="mt-0.5 truncate text-sm font-extrabold text-slate-800 dark:text-slate-200">{{ $contact['value'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-dashed border-slate-300 p-5 text-center text-sm leading-7 text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                راه‌های ارتباطی از پنل تنظیمات درباره ما قابل ثبت هستند.
                            </p>
                        @endforelse
                    </div>
                </aside>
            </div>
        </section>
    </main>
@endsection
