@extends('front.master')

@php
    $primaryCategory = $video->categories->first();
    $descriptionLength = mb_strlen(strip_tags((string) $video->body));
    $hasLongDescription = $descriptionLength > 650;
@endphp

@section('content')
    <main class="relative min-h-screen overflow-hidden bg-slate-50 pb-20 dark:bg-slate-950">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[680px] overflow-hidden" aria-hidden="true">
            <div class="absolute -right-40 top-8 h-96 w-96 rounded-full bg-brand-300/20 blur-[110px] dark:bg-brand-500/15"></div>
            <div class="absolute -left-32 top-36 h-[28rem] w-[28rem] rounded-full bg-violet-300/20 blur-[130px] dark:bg-violet-600/10"></div>
            <div class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-white via-white/60 to-transparent dark:from-slate-950 dark:via-slate-950/70"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <nav class="mb-5 flex min-w-0 items-center gap-2 overflow-hidden text-[11px] font-bold text-slate-500 dark:text-slate-400" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}" class="shrink-0 transition hover:text-brand-600 dark:hover:text-brand-300">خانه</a>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <a href="{{ route('videos.index') }}" class="shrink-0 transition hover:text-brand-600 dark:hover:text-brand-300">ویدیو سنتر</a>
                @if($primaryCategory)
                    <span class="text-slate-300 dark:text-slate-700">/</span>
                    <a href="{{ route('videos.categories', $primaryCategory->path) }}" class="hidden shrink-0 transition hover:text-brand-600 sm:block dark:hover:text-brand-300">{{ $primaryCategory->title }}</a>
                @endif
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span class="truncate text-slate-700 dark:text-slate-200">{{ $video->title }}</span>
            </nav>

            <div class="grid items-start gap-6 lg:grid-cols-12 xl:gap-7">
                <div class="min-w-0 space-y-6 lg:col-span-8">
                    <section class="relative">
                        <div class="pointer-events-none absolute -inset-3 rounded-[2.25rem] bg-gradient-to-l from-brand-500/15 via-violet-500/10 to-cyan-400/10 opacity-80 blur-2xl dark:opacity-60" aria-hidden="true"></div>

                        <div class="relative overflow-hidden rounded-[1.35rem] border border-slate-800 bg-slate-950 p-1.5 shadow-2xl shadow-slate-950/20 sm:rounded-[1.75rem] sm:p-2 dark:border-white/10 dark:shadow-black/50">
                            <div class="absolute inset-x-12 top-0 h-px bg-gradient-to-r from-transparent via-brand-400 to-transparent"></div>

                            <div class="relative aspect-video overflow-hidden rounded-[1rem] bg-black sm:rounded-[1.3rem]">
                                @if($video->source_type === \App\Domain\Video\Enums\VideoSourceTypeEnum::APARAT && $video->embed_url)
                                    <iframe
                                        src="{{ $video->embed_url }}"
                                        title="{{ $video->title }}"
                                        allowfullscreen
                                        allow="autoplay; fullscreen; picture-in-picture"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        class="absolute inset-0 h-full w-full border-0"
                                    ></iframe>
                                @elseif($video->source_type === \App\Domain\Video\Enums\VideoSourceTypeEnum::DIRECT && filled($video->video_url))
                                    <video
                                        class="absolute inset-0 h-full w-full bg-black object-contain"
                                        controls
                                        playsinline
                                        preload="metadata"
                                        poster="{{ $video->cover?->url }}"
                                        controlsList="nodownload"
                                    >
                                        <source src="{{ $video->video_url }}">
                                        مرورگر شما از پخش این ویدیو پشتیبانی نمی‌کند.
                                    </video>
                                @else
                                    @if($video->cover?->url)
                                        <img src="{{ $video->cover->url }}" alt="کاور {{ $video->title }}" class="absolute inset-0 h-full w-full object-cover opacity-35 blur-[2px]" fetchpriority="high">
                                    @endif
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(249,115,22,.16),transparent_46%)]"></div>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 px-6 text-center">
                                        <span class="grid h-16 w-16 place-items-center rounded-[1.35rem] border border-white/10 bg-white/5 text-brand-400 shadow-xl backdrop-blur-sm [&_svg]:h-7 [&_svg]:w-7">
                                            @include('icons.video-01')
                                        </span>
                                        <div>
                                            <p class="text-sm font-black text-white">ویدیو هنوز آماده پخش نیست</p>
                                            <p class="mt-1 text-xs text-slate-500">لینک پخش این ویدیو به‌زودی فعال می‌شود.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-3 px-2 pb-1 pt-2 text-[10px] font-bold text-slate-400 sm:px-3">
                                <span class="inline-flex items-center gap-2"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,.8)]"></span>MODDING TV</span>
                                <span class="flex items-center gap-3">
                                    <span>{{ $video->source_type->label() }}</span>
                                    @if($video->formatted_duration)<span dir="ltr" class="text-slate-200">{{ $video->formatted_duration }}</span>@endif
                                </span>
                            </div>
                        </div>
                    </section>

                    <section
                        x-data="{
                            copied: false,
                            async shareVideo() {
                                const shareData = { title: @js($video->title), url: @js($video->url) };
                                if (navigator.share) {
                                    try { await navigator.share(shareData); } catch (error) {}
                                    return;
                                }
                                try {
                                    await navigator.clipboard.writeText(shareData.url);
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 1800);
                                } catch (error) {}
                            }
                        }"
                        class="relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-5 shadow-sm backdrop-blur-xl sm:p-6 dark:border-slate-800 dark:bg-slate-900/85 dark:shadow-black/20"
                    >
                        <div class="pointer-events-none absolute -left-16 -top-16 h-44 w-44 rounded-full bg-brand-100/80 blur-3xl dark:bg-brand-500/10" aria-hidden="true"></div>

                        <div class="relative">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-[10px] font-black text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                    ویدیو سنتر
                                </span>
                                @foreach($video->categories as $category)
                                    <a href="{{ route('videos.categories', $category->path) }}" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-extrabold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/40 dark:hover:text-brand-300">{{ $category->title }}</a>
                                @endforeach
                            </div>

                            <h1 class="max-w-3xl text-xl font-black leading-[1.75] text-slate-950 sm:text-2xl lg:text-[1.7rem] dark:text-white">{{ $video->title }}</h1>

                            @if(filled($video->excerpt))
                                <p class="mt-3 max-w-3xl text-sm font-medium leading-7 text-slate-600 dark:text-slate-300">{{ strip_tags($video->excerpt) }}</p>
                            @endif

                            <div class="mt-5 flex flex-col gap-5 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 [&_svg]:h-4 [&_svg]:w-4">
                                    <span class="inline-flex items-center gap-1.5">@include('icons.user') {{ $video->author?->display_name ?? 'تیم مودینگ‌وی' }}</span>
                                    <span class="inline-flex items-center gap-1.5">@include('icons.calendar-03') {{ jalali_date($video->published_at, 'd F Y') }}</span>
                                    <span class="inline-flex items-center gap-1.5">@include('icons.view') {{ number_format(page_views($video)) }} بازدید</span>
                                    <a href="#comments" class="inline-flex items-center gap-1.5 transition hover:text-brand-600 dark:hover:text-brand-300">@include('icons.comment-01') {{ number_format($video->approved_comments_count) }} دیدگاه</a>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="shareVideo()"
                                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-xs font-black text-slate-700 transition hover:-translate-y-0.5 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 sm:w-auto dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4"
                                >
                                    @include('icons.share-08')
                                    <span x-text="copied ? 'لینک کپی شد' : 'اشتراک‌گذاری'"></span>
                                </button>
                            </div>
                        </div>
                    </section>

                    @if(filled($video->body) || $video->tags->isNotEmpty())
                        <section
                            x-data="{ expanded: {{ $hasLongDescription ? 'false' : 'true' }} }"
                            class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20"
                        >
                            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.right-to-left-list-bullet')</span>
                                <div>
                                    <h2 class="text-sm font-black text-slate-900 dark:text-white">درباره این ویدیو</h2>
                                    <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">توضیحات، نکات و اطلاعات تکمیلی</p>
                                </div>
                            </div>

                            @if(filled($video->body))
                                <div class="relative px-5 py-6 sm:px-6">
                                    <div
                                        class="tinymce-content text-sm leading-8 dark:text-slate-200 dark:prose-invert"
                                        :class="expanded ? '' : 'max-h-72 overflow-hidden'"
                                    >
                                        {!! $video->body !!}
                                    </div>

                                    @if($hasLongDescription)
                                        <div x-show="!expanded" class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-white via-white/95 to-transparent dark:from-slate-900 dark:via-slate-900/95"></div>
                                        <div class="relative mt-3 flex justify-center">
                                            <button type="button" x-on:click="expanded = !expanded" class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-brand-50 hover:text-brand-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                                <span x-text="expanded ? 'نمایش کمتر' : 'نمایش کامل توضیحات'"></span>
                                                <span class="transition" :class="expanded ? 'rotate-90' : ''">←</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($video->tags->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                                    <span class="ml-1 text-[11px] font-bold text-slate-500 dark:text-slate-400">برچسب‌ها</span>
                                    @foreach($video->tags as $tag)
                                        <a href="{{ route('videos.tags', $tag->path) }}" class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/40 dark:hover:text-brand-300">#{{ $tag->title }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    @if($video->commentsEnabled())
                        <section id="comments" class="scroll-mt-28 space-y-6">
                            @livewire('front.discussion.send-comment-form', ['commentable' => $video, 'key' => 'send-comment-form-'.$video->getMorphClass().'-'.$video->getKey()])
                            @livewire('front.discussion.comments-list', ['commentable' => $video])
                        </section>
                    @endif
                </div>

                <aside class="min-w-0 lg:col-span-4">
                    <div class="space-y-5 lg:sticky lg:top-28">
                        <section class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                            <div class="relative overflow-hidden border-b border-slate-100 p-5 dark:border-slate-800">
                                <div class="pointer-events-none absolute -left-10 -top-10 h-28 w-28 rounded-full bg-brand-200/60 blur-3xl dark:bg-brand-500/10"></div>
                                <div class="relative flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-950 text-brand-400 shadow-lg dark:bg-brand-500 dark:text-white [&_svg]:h-5 [&_svg]:w-5">@include('icons.play')</span>
                                        <div>
                                            <h2 class="text-sm font-black text-slate-900 dark:text-white">پیشنهاد برای تماشا</h2>
                                            <p class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">ویدیوهای مرتبط با این موضوع</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('videos.index') }}" class="text-[10px] font-black text-brand-600 transition hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">همه ویدیوها ←</a>
                                </div>
                            </div>

                            <div class="space-y-1.5 p-3">
                                @forelse($relatedVideos as $related)
                                    <a href="{{ $related->url }}" class="group grid grid-cols-[116px_minmax(0,1fr)] gap-3 rounded-2xl p-2 transition hover:bg-slate-50 sm:grid-cols-[136px_minmax(0,1fr)] lg:grid-cols-[116px_minmax(0,1fr)] xl:grid-cols-[138px_minmax(0,1fr)] dark:hover:bg-slate-800/70">
                                        <div class="relative aspect-video overflow-hidden rounded-xl bg-slate-950">
                                            @if($related->cover?->url)
                                                <img src="{{ $related->cover->url }}" alt="کاور {{ $related->title }}" loading="lazy" class="h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-105">
                                            @else
                                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(249,115,22,.28),transparent_50%),linear-gradient(135deg,#0f172a,#020617)]"></div>
                                            @endif
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                            <span class="absolute inset-0 m-auto grid h-9 w-9 place-items-center rounded-xl border border-white/20 bg-black/35 text-white opacity-90 backdrop-blur-sm transition group-hover:scale-110 group-hover:bg-brand-500 [&_svg]:h-4 [&_svg]:w-4">@include('icons.play')</span>
                                            @if($related->formatted_duration)
                                                <span dir="ltr" class="absolute bottom-1.5 left-1.5 rounded-md bg-black/70 px-1.5 py-0.5 text-[9px] font-black text-white">{{ $related->formatted_duration }}</span>
                                            @endif
                                        </div>

                                        <div class="flex min-w-0 flex-col justify-center">
                                            <h3 class="line-clamp-2 text-[11px] font-black leading-5 text-slate-800 transition group-hover:text-brand-600 sm:text-xs dark:text-slate-100 dark:group-hover:text-brand-300">{{ $related->title }}</h3>
                                            <div class="mt-2 flex items-center gap-2 text-[9px] font-bold text-slate-400">
                                                <span>{{ number_format(page_views($related)) }} بازدید</span>
                                                <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                                                <span>{{ jalali_date($related->published_at, 'Y/m/d') }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-4 py-10 text-center">
                                        <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-6 [&_svg]:w-6">@include('icons.video-01')</span>
                                        <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">فعلاً ویدیوی مرتبطی وجود ندارد.</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <section class="relative overflow-hidden rounded-[1.5rem] border border-slate-800 bg-slate-950 p-5 text-white shadow-xl dark:border-white/10">
                            <div class="pointer-events-none absolute -left-14 -top-14 h-40 w-40 rounded-full bg-violet-600/25 blur-3xl"></div>
                            <div class="pointer-events-none absolute -bottom-16 -right-10 h-36 w-36 rounded-full bg-brand-500/25 blur-3xl"></div>
                            <div class="relative">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black tracking-[0.16em] text-brand-400">NOW PLAYING</span>
                                    <span class="flex items-center gap-1.5 text-[9px] font-bold text-slate-500"><span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>آنلاین</span>
                                </div>
                                <p class="mt-4 line-clamp-2 text-sm font-black leading-7">{{ $video->title }}</p>
                                <div class="mt-5 grid grid-cols-3 divide-x divide-x-reverse divide-white/10 rounded-2xl border border-white/10 bg-white/5 py-3 text-center backdrop-blur-sm">
                                    <div><b class="block text-sm text-white">{{ number_format(page_views($video)) }}</b><span class="mt-1 block text-[9px] text-slate-500">بازدید</span></div>
                                    <div><b class="block text-sm text-white">{{ number_format($video->approved_comments_count) }}</b><span class="mt-1 block text-[9px] text-slate-500">دیدگاه</span></div>
                                    <div><b dir="ltr" class="block text-sm text-white">{{ $video->formatted_duration ?? '—' }}</b><span class="mt-1 block text-[9px] text-slate-500">مدت</span></div>
                                </div>
                            </div>
                        </section>
                    </div>
                </aside>
            </div>
        </div>
    </main>
@endsection
