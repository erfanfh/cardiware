@extends('front.master')

@php
    $videoSettingsDefaults = \App\Infrastructure\Services\Setting\Groups\VideosSettings::defaults();
    $displayFeatured = $featured ?: collect($videos->items())->first();
    $heroImage = get_setting('videos', 'hero_image')?->path ?: $displayFeatured?->cover?->url;
    $heroBadge = get_setting('videos', 'hero_badge') ?: $videoSettingsDefaults['hero_badge'];
    $heroTitle = get_setting('videos', 'hero_title') ?: $videoSettingsDefaults['hero_title'];
    $heroDescription = get_setting('videos', 'hero_description') ?: $videoSettingsDefaults['hero_description'];
    $heroPrimaryButton = get_setting('videos', 'hero_primary_button_title') ?: $videoSettingsDefaults['hero_primary_button_title'];
    $heroSecondaryButton = get_setting('videos', 'hero_secondary_button_title') ?: $videoSettingsDefaults['hero_secondary_button_title'];
    $featuredBadge = get_setting('videos', 'featured_badge') ?: $videoSettingsDefaults['featured_badge'];
    $archiveTitle = isset($term) ? $term->title : (get_setting('videos', 'archive_title') ?: $videoSettingsDefaults['archive_title']);
    $archiveDescription = isset($term) && filled($term->description)
        ? trim(strip_tags($term->description))
        : (get_setting('videos', 'archive_description') ?: $videoSettingsDefaults['archive_description']);
    $trendingTitle = get_setting('videos', 'trending_title') ?: $videoSettingsDefaults['trending_title'];
    $categoriesTitle = get_setting('videos', 'categories_title') ?: $videoSettingsDefaults['categories_title'];
    $followTitle = get_setting('videos', 'follow_title') ?: $videoSettingsDefaults['follow_title'];
    $followDescription = get_setting('videos', 'follow_description') ?: $videoSettingsDefaults['follow_description'];
    $followButtonTitle = get_setting('videos', 'follow_button_title') ?: $videoSettingsDefaults['follow_button_title'];
    $followUrlCandidate = trim((string) get_setting('videos', 'follow_button_url'));
    $followUrl = filter_var($followUrlCandidate, FILTER_VALIDATE_URL) || str_starts_with($followUrlCandidate, '/')
        ? $followUrlCandidate
        : null;
    $emptyTitle = get_setting('videos', 'empty_title') ?: $videoSettingsDefaults['empty_title'];
    $emptyDescription = get_setting('videos', 'empty_description') ?: $videoSettingsDefaults['empty_description'];
    $sortUrl = static fn (string $value) => request()->url() . '?' . http_build_query([
        ...request()->except(['sort', 'page']),
        'sort' => $value,
    ]);
@endphp

@section('content')
    <main class="relative min-h-screen overflow-hidden bg-slate-50 pb-20 dark:bg-slate-950">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[700px] overflow-hidden" aria-hidden="true">
            <div
                class="absolute -right-44 top-10 h-96 w-96 rounded-full bg-brand-300/25 blur-[120px] dark:bg-brand-500/15"></div>
            <div
                class="absolute -left-40 top-52 h-[28rem] w-[28rem] rounded-full bg-violet-300/20 blur-[140px] dark:bg-violet-600/10"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <x-front.breadcrumbs
                nav-class="mb-5 flex items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400"
                link-class="transition hover:text-brand-700 dark:hover:text-brand-300"
                current-class="truncate text-slate-700 dark:text-slate-200"
                separator-class="text-slate-300 dark:text-slate-700"
            />

            <section
                class="relative overflow-hidden rounded-[1.5rem] border border-slate-800/80 bg-slate-950 shadow-2xl shadow-slate-950/20 sm:rounded-[2rem] dark:border-white/10 dark:shadow-black/50">
                <div class="absolute inset-0">
                    @if($heroImage)
                        <img src="{{ $heroImage }}" alt="{{ $heroTitle }}" class="h-full w-full object-cover opacity-35"
                             fetchpriority="high">
                    @else
                        <div
                            class="h-full w-full bg-[radial-gradient(circle_at_78%_18%,rgba(249,115,22,.28),transparent_35%),radial-gradient(circle_at_15%_85%,rgba(139,92,246,.25),transparent_38%),linear-gradient(135deg,#0f172a,#020617)]"></div>
                    @endif
                    <div
                        class="absolute inset-0 bg-gradient-to-l from-slate-950 via-slate-950/85 to-slate-950/45"></div>
                    <div
                        class="absolute inset-0 opacity-[.07] [background-image:linear-gradient(rgba(255,255,255,.65)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.65)_1px,transparent_1px)] [background-size:42px_42px]"></div>
                </div>

                <div class="relative grid items-stretch gap-6 p-5 sm:p-7 lg:grid-cols-12 lg:p-9">
                    <div
                        class="flex flex-col justify-center {{ $displayFeatured ? 'lg:col-span-7' : 'lg:col-span-12' }}">
                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-3 py-2 text-xs font-black text-white/90 backdrop-blur sm:text-sm">
                            <span
                                class="grid h-6 w-6 place-items-center rounded-lg bg-brand-500/20 text-brand-300 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.video-01')</span>
                            {{ $heroBadge }}
                        </div>

                        <h1 class="mt-5 max-w-3xl text-2xl font-black leading-[1.65] text-white sm:text-3xl lg:text-[2.1rem]">{{ $heroTitle }}</h1>
                        <p class="mt-4 max-w-2xl text-sm font-medium leading-8 text-slate-300 sm:text-[15px]">{{ $heroDescription }}</p>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a href="#video-archive"
                               class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-950/30 transition hover:-translate-y-0.5 hover:bg-brand-600">
                                {{ $heroPrimaryButton }} <span>←</span>
                            </a>
                            <a href="{{ $sortUrl('popular') }}#video-archive"
                               class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:bg-white/15">
                                {{ $heroSecondaryButton }}
                            </a>
                        </div>

                        <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-2xl border border-white/10 bg-white/[.08] p-3.5 backdrop-blur">
                                <div class="text-[10px] font-black text-slate-400">کل ویدیوها</div>
                                <div class="mt-2 text-xl font-black text-white">{{ number_format($totalVideos) }}</div>
                            </div>
                            @foreach($categories->take(3) as $heroCategory)
                                <a href="{{ route('videos.categories', $heroCategory->path) }}"
                                   class="rounded-2xl border border-white/10 bg-white/[.08] p-3.5 backdrop-blur transition hover:border-brand-400/40 hover:bg-white/[.12]">
                                    <div
                                        class="truncate text-[10px] font-black text-slate-400">{{ $heroCategory->title }}</div>
                                    <div
                                        class="mt-2 text-xl font-black text-white">{{ number_format($heroCategory->videos_count) }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if($displayFeatured)
                        <div class="lg:col-span-5">
                            <a href="{{ $displayFeatured->url }}"
                               class="group flex h-full flex-col overflow-hidden rounded-[1.35rem] border border-white/10 bg-white/10 shadow-2xl backdrop-blur transition hover:border-brand-400/30">
                                <div class="relative aspect-video overflow-hidden bg-slate-900">
                                    @if($displayFeatured->cover?->url)
                                        <x-front.responsive-image
                                            :media="$displayFeatured->cover"
                                            :fallback="$displayFeatured->cover->url"
                                            :alt="$displayFeatured->title"
                                            variant="large"
                                            class="h-full w-full object-cover"
                                            wrapper-class="absolute inset-0 h-full w-full transform-gpu will-change-transform transition-transform duration-700 ease-out group-hover:scale-105"
                                            sizes="(max-width: 1024px) 100vw, 520px"
                                            :lazy="false"
                                            fetchpriority="high"
                                        />
                                    @else
                                        <div
                                            class="absolute inset-0 bg-[radial-gradient(circle_at_30%_25%,rgba(249,115,22,.35),transparent_38%),linear-gradient(135deg,#334155,#020617)]"></div>
                                    @endif
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/15 to-transparent"></div>
                                    <div class="absolute inset-x-3 top-3 flex items-center justify-between gap-2">
                                        <span
                                            class="rounded-xl bg-white/90 px-2.5 py-1 text-[10px] font-black text-slate-900">{{ $featuredBadge }}</span>
                                        @if($displayFeatured->formatted_duration)
                                            <span dir="ltr"
                                                  class="rounded-xl bg-black/60 px-2.5 py-1 text-[10px] font-black text-white backdrop-blur">{{ $displayFeatured->formatted_duration }}</span>
                                        @endif
                                    </div>
                                    <span
                                        class="absolute inset-0 m-auto grid h-16 w-16 place-items-center rounded-full bg-brand-600 text-white shadow-2xl transition group-hover:scale-110 group-hover:bg-brand-500 [&_svg]:h-7 [&_svg]:w-7">@include('icons.play')</span>
                                </div>

                                <div class="flex grow flex-col p-5">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($displayFeatured->categories->take(2) as $category)
                                            <span
                                                class="rounded-xl border border-white/10 bg-white/10 px-2 py-1 text-[10px] font-black text-brand-200">{{ $category->title }}</span>
                                        @endforeach
                                    </div>
                                    <h2 class="mt-3 line-clamp-2 text-base font-black leading-8 text-white sm:text-lg">{{ $displayFeatured->title }}</h2>
                                    @if(filled($displayFeatured->excerpt))
                                        <p class="mt-2 line-clamp-2 text-xs font-medium leading-6 text-slate-300">{{ strip_tags($displayFeatured->excerpt) }}</p>
                                    @endif
                                    <div
                                        class="mt-auto flex items-center justify-between gap-3 pt-4 text-[10px] font-bold text-slate-400">
                                        <span>{{ approximate_view(public_page_views($displayFeatured)) }} بازدید</span>
                                        <span class="font-black text-brand-200">مشاهده ویدیو ←</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            <section id="video-archive" class="mt-6 grid scroll-mt-24 gap-6 lg:grid-cols-12 lg:items-start">
                <aside class="order-2 space-y-5 lg:order-1 lg:col-span-3">
                    @if($trendingVideos->isNotEmpty())
                        <div
                            class="rounded-[1.25rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="grid h-9 w-9 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.fire')</span>
                                    <h2 class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $trendingTitle }}</h2>
                                </div>
                            </div>

                            <div class="mt-4 space-y-2">
                                @foreach($trendingVideos as $trending)
                                    <a href="{{ $trending->url }}"
                                       class="group flex gap-3 rounded-2xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                                        <div
                                            class="relative w-28 shrink-0 overflow-hidden rounded-xl bg-slate-950 sm:w-20">
                                            @if($trending->cover?->url)
                                                <x-front.responsive-image :media="$trending->cover"
                                                                          :fallback="$trending->cover->url"
                                                                          :alt="$trending->title" variant="medium"
                                                                          class="h-full w-full object-cover"
                                                                          wrapper-class="absolute inset-0 h-full w-full transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-105"
                                                                          sizes="128px"/>
                                            @else
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-br from-brand-500/40 to-slate-950"></div>
                                            @endif
                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/65 to-transparent"></div>
                                            @if($trending->formatted_duration)
                                                <span dir="ltr"
                                                      class="absolute bottom-1.5 left-1.5 rounded-md bg-black/70 px-1.5 py-0.5 text-[8px] font-black text-white">{{ $trending->formatted_duration }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0 py-0.5">
                                            <h3 class="line-clamp-2 text-xs font-black leading-6 text-slate-800 transition group-hover:text-brand-700 dark:text-slate-200 dark:group-hover:text-brand-300">{{ $trending->title }}</h3>
                                            <div
                                                class="mt-1.5 flex items-center gap-2 text-[9px] font-bold text-slate-400">
                                                <span>{{ approximate_view(public_page_views($trending)) }} بازدید</span>
                                                <span
                                                    class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                                <span>{{ jalali_date($trending->published_at, 'Y/m/d') }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($categories->isNotEmpty())
                        <div
                            class="rounded-[1.25rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <div class="flex items-center gap-3">
                                <span
                                    class="grid h-9 w-9 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.folder-library')</span>
                                <h2 class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $categoriesTitle }}</h2>
                            </div>
                            <div class="mt-4 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-1">
                                @foreach($categories as $category)
                                    <a href="{{ route('videos.categories', $category->path) }}"
                                       class="flex items-center justify-between gap-3 rounded-2xl border border-transparent bg-slate-50 p-3.5 transition hover:border-slate-200 hover:bg-white hover:shadow-sm dark:bg-slate-800/60 dark:hover:border-slate-700 dark:hover:bg-slate-800">
                                        <span
                                            class="text-xs font-black text-slate-800 dark:text-slate-200">{{ $category->title }}</span>
                                        <span
                                            class="rounded-lg bg-white px-2 py-1 text-[9px] font-black text-slate-500 shadow-sm dark:bg-slate-900 dark:text-slate-400">{{ number_format($category->videos_count) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>

                <div class="order-1 min-w-0 lg:order-2 lg:col-span-9">
                    <div
                        class="rounded-[1.25rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.video-01')</span>
                                <div class="min-w-0">
                                    <h2 class="truncate text-sm font-black text-slate-900 sm:text-base dark:text-slate-100">{{ $archiveTitle }}</h2>
                                    <p class="mt-1 line-clamp-1 text-[10px] font-medium text-slate-500 sm:text-xs dark:text-slate-400">{{ $archiveDescription }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ $sortUrl('latest') }}#video-archive"
                                   class="rounded-xl border px-3 py-2 text-[10px] font-black transition sm:text-xs {{ $sort === 'latest' ? 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">جدیدترین</a>
                                <a href="{{ $sortUrl('popular') }}#video-archive"
                                   class="rounded-xl border px-3 py-2 text-[10px] font-black transition sm:text-xs {{ $sort === 'popular' ? 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">پربازدیدترین</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse($videos as $video)
                            @include('front.videos.partials.card', ['video' => $video])
                        @empty
                            <div
                                class="rounded-[1.25rem] border border-dashed border-slate-300 bg-white p-10 text-center sm:col-span-2 xl:col-span-3 dark:border-slate-700 dark:bg-slate-900">
                                <span
                                    class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-6 [&_svg]:w-6">@include('icons.video-01')</span>
                                <h2 class="mt-4 text-sm font-black text-slate-800 dark:text-slate-200">{{ $emptyTitle }}</h2>
                                <p class="mt-2 text-xs font-medium leading-6 text-slate-500 dark:text-slate-400">{{ $emptyDescription }}</p>
                            </div>
                        @endforelse
                    </div>

                    <div
                        class="relative mt-6 overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <div
                            class="absolute -left-8 -top-10 h-28 w-28 rounded-[3rem] bg-brand-100/80 dark:bg-brand-500/10"></div>
                        <div
                            class="absolute bottom-0 right-1/3 h-20 w-20 rounded-full bg-violet-100/70 blur-2xl dark:bg-violet-500/10"></div>
                        <div
                            class="relative flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                            <div>
                                <h2 class="text-sm font-black text-slate-900 sm:text-base dark:text-slate-100">{{ $followTitle }}</h2>
                                <p class="mt-2 max-w-xl text-xs font-medium leading-7 text-slate-600 sm:text-sm dark:text-slate-400">{{ $followDescription }}</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                                @if($followUrl)
                                    <a href="{{ $followUrl }}"
                                       class="rounded-2xl bg-brand-500 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-brand-500/15 transition hover:bg-brand-600"
                                       @if(str_starts_with($followUrl, 'http')) target="_blank"
                                       rel="noopener noreferrer" @endif>{{ $followButtonTitle }}</a>
                                @endif
                                <a href="{{ route('videos.index') }}"
                                   class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-brand-700 transition hover:border-brand-200 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-900 dark:text-brand-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">آرشیو
                                    کامل</a>
                            </div>
                        </div>
                    </div>

                    @if($videos->hasPages())
                        <div
                            class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <x-front.list.pagination :model="$videos"/>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>
@endsection
