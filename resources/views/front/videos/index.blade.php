@extends('front.master')

@php
    $isTaxonomyPage = isset($term);
    $pageTitle = $isTaxonomyPage ? $term->title : 'ویدیو سنتر مودینگ‌وی';
    $pageDescription = $isTaxonomyPage && filled($term->description)
        ? strip_tags($term->description)
        : 'آموزش‌های تخصصی، معرفی مادها، تریلرها و تازه‌ترین ویدیوهای دنیای بازی را یک‌جا تماشا کنید.';
@endphp

@section('content')
    <main class="relative min-h-screen overflow-hidden bg-slate-50 pb-20 dark:bg-slate-950">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[760px] overflow-hidden" aria-hidden="true">
            <div class="absolute -right-36 top-16 h-[26rem] w-[26rem] rounded-full bg-brand-300/20 blur-[120px] dark:bg-brand-500/15"></div>
            <div class="absolute -left-44 top-44 h-[30rem] w-[30rem] rounded-full bg-violet-300/20 blur-[140px] dark:bg-violet-600/10"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <nav class="mb-5 flex items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}" class="transition hover:text-brand-600 dark:hover:text-brand-300">خانه</a>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                @if($isTaxonomyPage)
                    <a href="{{ route('videos.index') }}" class="transition hover:text-brand-600 dark:hover:text-brand-300">ویدیو سنتر</a>
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                @endif
                <span class="truncate text-slate-700 dark:text-slate-200">{{ $pageTitle }}</span>
            </nav>

            <section class="relative overflow-hidden rounded-[1.75rem] border border-slate-800 bg-slate-950 text-white shadow-2xl shadow-slate-950/15 sm:rounded-[2rem] dark:border-white/10 dark:shadow-black/40">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute inset-0 opacity-70 [background-image:linear-gradient(rgba(255,255,255,.025)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.025)_1px,transparent_1px)] [background-size:42px_42px]"></div>
                    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-500/20 blur-[90px]"></div>
                    <div class="absolute -bottom-40 left-10 h-[28rem] w-[28rem] rounded-full bg-violet-600/20 blur-[120px]"></div>
                    <div class="absolute inset-x-20 top-0 h-px bg-gradient-to-r from-transparent via-brand-400 to-transparent"></div>
                </div>

                <div class="relative grid gap-8 p-5 sm:p-7 lg:grid-cols-12 lg:items-center lg:p-9 xl:p-10">
                    <div class="lg:col-span-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-brand-400/25 bg-brand-500/10 px-3 py-1.5 text-[10px] font-black tracking-[0.12em] text-brand-300 backdrop-blur-sm">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-400 shadow-[0_0_10px_rgba(251,146,60,.8)]"></span>
                            MODDING TV
                        </div>

                        <h1 class="mt-5 max-w-3xl text-2xl font-black leading-[1.65] sm:text-3xl lg:text-4xl lg:leading-[1.55]">{{ $pageTitle }}</h1>
                        <p class="mt-4 max-w-2xl text-sm font-medium leading-7 text-slate-300 sm:text-[15px] sm:leading-8">{{ $pageDescription }}</p>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a href="#video-archive" class="inline-flex h-11 items-center gap-2 rounded-2xl bg-brand-500 px-5 text-xs font-black text-white shadow-lg shadow-brand-950/20 transition hover:-translate-y-0.5 hover:bg-brand-400">شروع تماشا <span>←</span></a>
                            @if($featured && ! $isTaxonomyPage)
                                <a href="{{ $featured->url }}" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 text-xs font-black text-slate-200 backdrop-blur-sm transition hover:border-white/20 hover:bg-white/10 hover:text-white">
                                    ویدیوی ویژه
                                    <span class="grid h-6 w-6 place-items-center rounded-lg bg-white/10 [&_svg]:h-3 [&_svg]:w-3">@include('icons.play')</span>
                                </a>
                            @endif
                        </div>

                        <div class="mt-7 grid max-w-2xl grid-cols-3 gap-2.5 sm:gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/[.055] p-3 backdrop-blur-sm sm:p-4"><b class="block text-lg font-black sm:text-2xl">{{ number_format($totalVideos) }}</b><span class="mt-1 block text-[9px] font-bold text-slate-400 sm:text-[10px]">کل ویدیوها</span></div>
                            <div class="rounded-2xl border border-white/10 bg-white/[.055] p-3 backdrop-blur-sm sm:p-4"><b class="block text-lg font-black sm:text-2xl">{{ number_format($categories->count()) }}</b><span class="mt-1 block text-[9px] font-bold text-slate-400 sm:text-[10px]">دسته‌بندی</span></div>
                            <div class="rounded-2xl border border-white/10 bg-white/[.055] p-3 backdrop-blur-sm sm:p-4"><b class="block text-lg font-black sm:text-2xl">{{ number_format($videos->total()) }}</b><span class="mt-1 block text-[9px] font-bold text-slate-400 sm:text-[10px]">نتیجه فعلی</span></div>
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        @if($featured && ! $isTaxonomyPage)
                            <a href="{{ $featured->url }}" class="group relative block overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 p-1.5 shadow-2xl backdrop-blur-sm sm:p-2">
                                <div class="relative aspect-video overflow-hidden rounded-[1.15rem] bg-slate-900">
                                    @if($featured->cover?->url)
                                        <x-front.responsive-image :media="$featured->cover" :fallback="$featured->cover->url" :alt="$featured->title" variant="large" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" wrapper-class="absolute inset-0 h-full w-full" :lazy="false" :async="false" :skeleton="false" fetchpriority="high" sizes="(max-width: 1024px) 100vw, 40vw"/>
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_25%,rgba(249,115,22,.35),transparent_40%),linear-gradient(135deg,#1e293b,#020617)]"></div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                                    <span class="absolute right-3 top-3 rounded-full border border-white/15 bg-black/35 px-2.5 py-1 text-[9px] font-black text-white backdrop-blur-md">پیشنهاد ویژه</span>
                                    @if($featured->formatted_duration)<span dir="ltr" class="absolute bottom-3 left-3 rounded-lg bg-black/65 px-2 py-1 text-[10px] font-black">{{ $featured->formatted_duration }}</span>@endif
                                    <span class="absolute inset-0 m-auto grid h-14 w-14 place-items-center rounded-[1.1rem] border border-white/20 bg-white/15 text-white shadow-xl backdrop-blur-md transition group-hover:scale-110 group-hover:bg-brand-500 [&_svg]:h-6 [&_svg]:w-6">@include('icons.play')</span>
                                </div>
                                <div class="p-3 sm:p-4">
                                    <h2 class="line-clamp-2 text-sm font-black leading-7 text-white sm:text-base">{{ $featured->title }}</h2>
                                    <div class="mt-3 flex items-center justify-between text-[10px] font-bold text-slate-400"><span>{{ number_format(page_views($featured)) }} بازدید</span><span class="text-brand-300 transition group-hover:text-brand-200">تماشای ویدیو ←</span></div>
                                </div>
                            </a>
                        @else
                            <div class="relative min-h-72 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                                <div class="absolute -left-14 -top-14 h-48 w-48 rounded-full bg-violet-500/20 blur-3xl"></div><div class="absolute -bottom-16 -right-12 h-44 w-44 rounded-full bg-brand-500/20 blur-3xl"></div>
                                <div class="relative flex h-full min-h-60 flex-col justify-between">
                                    <span class="grid h-14 w-14 place-items-center rounded-[1.15rem] border border-white/10 bg-white/5 text-brand-300 [&_svg]:h-7 [&_svg]:w-7">@include('icons.video-01')</span>
                                    <div><p class="text-[10px] font-black tracking-[0.14em] text-slate-500">VIDEO COLLECTION</p><h2 class="mt-3 text-xl font-black leading-9">{{ $pageTitle }}</h2><p class="mt-2 text-xs leading-6 text-slate-400">{{ number_format($videos->total()) }} ویدیو در این مجموعه برای شما آماده شده است.</p></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="mt-6" aria-label="دسته‌بندی ویدیوها">
                <div class="flex gap-2 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <a href="{{ route('videos.index') }}" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-2xl border px-4 text-xs font-black transition {{ ! $isTaxonomyPage ? 'border-slate-950 bg-slate-950 text-white shadow-lg dark:border-brand-500 dark:bg-brand-500' : 'border-slate-200 bg-white text-slate-600 hover:border-brand-300 hover:text-brand-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/40 dark:hover:text-brand-300' }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>همه ویدیوها</a>
                    @foreach($categories as $category)
                        @php($isActiveCategory = $isTaxonomyPage && $term->is($category))
                        <a href="{{ route('videos.categories', $category->path) }}" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-2xl border px-4 text-xs font-black transition {{ $isActiveCategory ? 'border-brand-500 bg-brand-500 text-white shadow-lg shadow-brand-500/15' : 'border-slate-200 bg-white text-slate-600 hover:border-brand-300 hover:text-brand-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/40 dark:hover:text-brand-300' }}">{{ $category->title }}<span class="rounded-lg bg-black/5 px-1.5 py-0.5 text-[9px] opacity-70 dark:bg-white/10">{{ number_format($category->videos_count) }}</span></a>
                    @endforeach
                </div>
            </section>

            <section id="video-archive" class="mt-6 grid scroll-mt-24 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="min-w-0">
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-xl sm:p-5 dark:border-slate-800 dark:bg-slate-900/85 dark:shadow-black/20">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.video-01')</span>
                                <div><h2 class="text-sm font-black text-slate-900 dark:text-white">{{ $isTaxonomyPage ? 'ویدیوهای '.$term->title : 'تازه‌های ویدیو سنتر' }}</h2><p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">{{ number_format($videos->total()) }} ویدیو برای تماشا</p></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1 dark:bg-slate-950">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest', 'page' => 1]) }}" class="inline-flex h-9 items-center justify-center rounded-xl px-3 text-[10px] font-black transition {{ $sort === 'latest' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">جدیدترین</a>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'popular', 'page' => 1]) }}" class="inline-flex h-9 items-center justify-center rounded-xl px-3 text-[10px] font-black transition {{ $sort === 'popular' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-800 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">محبوب‌ترین</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @forelse($videos as $video)
                            @include('front.videos.partials.card', ['video' => $video])
                        @empty
                            <div class="col-span-full overflow-hidden rounded-[1.5rem] border border-dashed border-slate-300 bg-white/70 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/60">
                                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-7 [&_svg]:w-7">@include('icons.video-01')</span><h3 class="mt-4 text-sm font-black text-slate-800 dark:text-slate-200">هنوز ویدیویی منتشر نشده است</h3><p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">به‌زودی ویدیوهای تازه‌ای به این بخش اضافه می‌شود.</p>
                            </div>
                        @endforelse
                    </div>
                    @if($videos->hasPages())<div class="mt-8">{{ $videos->links() }}</div>@endif
                </div>

                <aside class="min-w-0 space-y-5 lg:sticky lg:top-28">
                    <section class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <div class="flex items-center gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-950 text-brand-400 dark:bg-brand-500 dark:text-white [&_svg]:h-5 [&_svg]:w-5">@include('icons.fire')</span>
                            <div><h2 class="text-sm font-black text-slate-900 dark:text-white">پربازدیدهای ویدیو سنتر</h2><p class="mt-0.5 text-[9px] text-slate-500 dark:text-slate-400">انتخاب کاربران مودینگ‌وی</p></div>
                        </div>
                        <div class="space-y-1 p-3">
                            @forelse($trendingVideos as $position => $trending)
                                <a href="{{ $trending->url }}" class="group grid grid-cols-[112px_minmax(0,1fr)] gap-3 rounded-2xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                                    <div class="relative aspect-video overflow-hidden rounded-xl bg-slate-950">
                                        @if($trending->cover?->url)
                                            <x-front.responsive-image :media="$trending->cover" :fallback="$trending->cover->url" :alt="$trending->title" variant="medium" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" wrapper-class="absolute inset-0 h-full w-full" sizes="112px"/>
                                        @else
                                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_25%,rgba(249,115,22,.3),transparent_45%),linear-gradient(135deg,#1e293b,#020617)]"></div>
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent"></div><span class="absolute right-1.5 top-1.5 grid h-6 w-6 place-items-center rounded-lg bg-brand-500 text-[9px] font-black text-white shadow-lg">{{ $position + 1 }}</span>@if($trending->formatted_duration)<span dir="ltr" class="absolute bottom-1.5 left-1.5 rounded-md bg-black/70 px-1.5 py-0.5 text-[8px] font-black text-white">{{ $trending->formatted_duration }}</span>@endif
                                    </div>
                                    <div class="flex min-w-0 flex-col justify-center"><h3 class="line-clamp-2 text-[11px] font-black leading-5 text-slate-800 transition group-hover:text-brand-600 dark:text-slate-100 dark:group-hover:text-brand-300">{{ $trending->title }}</h3><div class="mt-2 flex items-center gap-2 text-[9px] font-bold text-slate-400"><span>{{ number_format(page_views($trending)) }} بازدید</span><span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-700"></span><span>{{ number_format($trending->approved_comments_count) }} دیدگاه</span></div></div>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-xs text-slate-500 dark:text-slate-400">هنوز ویدیویی برای نمایش وجود ندارد.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="relative overflow-hidden rounded-[1.5rem] border border-slate-800 bg-slate-950 p-5 text-white shadow-xl dark:border-white/10">
                        <div class="pointer-events-none absolute -left-16 -top-16 h-44 w-44 rounded-full bg-violet-600/25 blur-3xl"></div><div class="pointer-events-none absolute -bottom-16 -right-10 h-40 w-40 rounded-full bg-brand-500/25 blur-3xl"></div>
                        <div class="relative"><span class="text-[9px] font-black tracking-[0.16em] text-brand-400">EXPLORE MORE</span><h2 class="mt-3 text-lg font-black leading-8">دنیای مادینگ را تصویری دنبال کن</h2><p class="mt-2 text-xs leading-6 text-slate-400">از آموزش نصب تا معرفی تازه‌ترین مادها؛ همه‌چیز برای یک تجربه بهتر از بازی.</p><a href="#video-archive" class="mt-5 inline-flex h-10 items-center gap-2 rounded-xl bg-white px-4 text-[10px] font-black text-slate-950 transition hover:bg-brand-400 hover:text-white">مشاهده آرشیو <span>←</span></a></div>
                    </section>
                </aside>
            </section>
        </div>
    </main>
@endsection
