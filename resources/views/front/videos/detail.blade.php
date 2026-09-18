@extends('front.master')

@php
    use App\Domain\Video\Enums\VideoSourceTypeEnum;
    use App\Infrastructure\Services\Setting\Groups\VideosSettings;

    $settingsDefaults = VideosSettings::defaults();
    $coverUrl = $video->cover?->url;
    $excerpt = trim(strip_tags((string) $video->excerpt));
    $description = filled($video->body) ? $video->body : null;
    $views = approximate_view(public_page_views($video));
    $authorName = $video->author?->display_name
        ?: trim((string) $video->author?->full_name)
        ?: $video->author?->username
        ?: 'تیم مودینگ وی';
    $authorInitial = mb_substr($authorName, 0, 1);
    $isAparat = $video->source_type === VideoSourceTypeEnum::APARAT && filled($video->embed_url);
    $isDirect = $video->source_type === VideoSourceTypeEnum::DIRECT && filled($video->video_url);
    $followTitle = get_setting('videos', 'follow_title') ?: $settingsDefaults['follow_title'];
    $followDescription = get_setting('videos', 'follow_description') ?: $settingsDefaults['follow_description'];
    $followButtonTitle = get_setting('videos', 'follow_button_title') ?: $settingsDefaults['follow_button_title'];
    $followUrlCandidate = trim((string) get_setting('videos', 'follow_button_url'));
    $followUrl = filter_var($followUrlCandidate, FILTER_VALIDATE_URL) || str_starts_with($followUrlCandidate, '/')
        ? $followUrlCandidate
        : null;
@endphp

@section('content')
    <main class="relative min-h-screen overflow-hidden bg-slate-50 pb-20 dark:bg-slate-950">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[52rem] overflow-hidden" aria-hidden="true">
            <div class="absolute -right-40 top-10 h-96 w-96 rounded-full bg-brand-300/20 blur-[130px] dark:bg-brand-500/10"></div>
            <div class="absolute -left-44 top-72 h-[30rem] w-[30rem] rounded-full bg-violet-300/15 blur-[150px] dark:bg-violet-600/10"></div>
            <div class="absolute inset-x-0 top-0 h-80 opacity-[.035] dark:opacity-[.045]
                        [background-image:linear-gradient(rgba(15,23,42,.75)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,.75)_1px,transparent_1px)]
                        [background-size:44px_44px] dark:[background-image:linear-gradient(rgba(255,255,255,.6)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.6)_1px,transparent_1px)]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <nav class="mb-5 flex min-w-0 items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400"
                 aria-label="مسیر صفحه">
                <a href="{{ route('home') }}" class="shrink-0 transition hover:text-brand-700 dark:hover:text-brand-300">خانه</a>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <a href="{{ route('videos.index') }}" class="shrink-0 transition hover:text-brand-700 dark:hover:text-brand-300">ویدیو سنتر</a>
                @if($video->categories->first())
                    <span class="hidden text-slate-300 sm:inline dark:text-slate-700">/</span>
                    <a href="{{ route('videos.categories', $video->categories->first()->path) }}"
                       class="hidden shrink-0 transition hover:text-brand-700 sm:inline dark:hover:text-brand-300">{{ $video->categories->first()->title }}</a>
                @endif
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span class="truncate text-slate-700 dark:text-slate-200">{{ $video->title }}</span>
            </nav>

            <div data-smart-sidebar-layout class="grid grid-cols-1 gap-6 lg:grid-cols-10 lg:items-start">
                <div data-smart-sidebar-content class="min-w-0 space-y-6 lg:col-span-7">
                    <section class="overflow-hidden rounded-[1.4rem] border border-slate-800/90 bg-slate-950 shadow-2xl shadow-slate-950/15 sm:rounded-[2rem] dark:border-white/10 dark:shadow-black/50"
                             aria-label="پخش {{ $video->title }}">
                        <div class="relative aspect-video overflow-hidden bg-slate-950">
                            @if($isAparat)
                                <iframe
                                    src="{{ $video->embed_url }}"
                                    title="{{ $video->title }}"
                                    class="absolute inset-0 h-full w-full border-0"
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                    allowfullscreen
                                    loading="eager"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                ></iframe>
                            @elseif($isDirect)
                                <video
                                    class="absolute inset-0 h-full w-full bg-black object-contain"
                                    controls
                                    playsinline
                                    preload="metadata"
                                    @if($coverUrl) poster="{{ $coverUrl }}" @endif
                                    title="{{ $video->title }}"
                                >
                                    <source src="{{ $video->video_url }}">
                                    مرورگر شما امکان پخش این ویدیو را ندارد.
                                </video>
                            @else
                                @if($coverUrl)
                                    <x-front.responsive-image
                                        :media="$video->cover"
                                        :fallback="$coverUrl"
                                        :alt="$video->title"
                                        variant="large"
                                        class="h-full w-full object-cover opacity-55"
                                        wrapper-class="absolute inset-0 h-full w-full"
                                        sizes="(max-width: 1024px) 100vw, 850px"
                                        :lazy="false"
                                        fetchpriority="high"
                                    />
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(249,115,22,.3),transparent_36%),radial-gradient(circle_at_82%_80%,rgba(139,92,246,.22),transparent_38%),linear-gradient(135deg,#1e293b,#020617)]"></div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/55 to-slate-950/20"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
                                    <span class="grid h-16 w-16 place-items-center rounded-full border border-white/10 bg-white/10 text-white backdrop-blur [&_svg]:h-7 [&_svg]:w-7">@include('icons.video-01')</span>
                                    <p class="mt-4 text-sm font-black text-white">منبع پخش این ویدیو هنوز در دسترس نیست</p>
                                    <p class="mt-2 max-w-md text-xs font-medium leading-6 text-slate-300">اطلاعات و توضیحات ویدیو را می‌توانید در ادامه همین صفحه مشاهده کنید.</p>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-[1.4rem] border border-slate-200 bg-white shadow-sm sm:rounded-[1.75rem] dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <div class="p-5 sm:p-7">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-brand-100 bg-brand-50 px-2.5 py-1.5 text-[10px] font-black text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-3.5 [&_svg]:w-3.5">
                                    @include('icons.video-01')
                                    ویدیو سنتر
                                </span>
                                @foreach($video->categories as $category)
                                    <a href="{{ route('videos.categories', $category->path) }}"
                                       class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[10px] font-black text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">{{ $category->title }}</a>
                                @endforeach
                            </div>

                            <h1 class="mt-4 text-xl font-black leading-[1.75] text-slate-950 sm:text-2xl lg:text-[1.7rem] dark:text-white">{{ $video->title }}</h1>

                            @if($excerpt !== '')
                                <p class="mt-3 text-sm font-medium leading-8 text-slate-600 sm:text-[15px] dark:text-slate-400">{{ $excerpt }}</p>
                            @endif

                            <div class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if($video->author?->avatar?->url)
                                        <img src="{{ $video->author->avatar->url }}" alt="{{ $authorName }}"
                                             class="h-11 w-11 shrink-0 rounded-2xl border border-slate-200 bg-slate-50 object-cover dark:border-slate-700 dark:bg-slate-800">
                                    @else
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-sm font-black text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">{{ $authorInitial }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-black text-slate-800 sm:text-sm dark:text-slate-200">{{ $authorName }}</p>
                                        <p class="mt-1 text-[10px] font-bold text-slate-400">منتشرکننده ویدیو</p>
                                    </div>
                                </div>

                                <button type="button" x-data
                                        x-on:click="$dispatch('open-modal', 'video-share-{{ $video->id }}')"
                                        class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">
                                    @include('icons.share-08')
                                    اشتراک‌گذاری
                                </button>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/60">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.calendar-03') تاریخ انتشار</div>
                                    <div class="mt-2 text-xs font-black text-slate-700 dark:text-slate-200">{{ $video->published_at ? jalali_date($video->published_at, 'd F Y') : 'نامشخص' }}</div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/60">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.view') بازدید</div>
                                    <div class="mt-2 text-xs font-black text-slate-700 dark:text-slate-200">{{ $views ?: 0 }} بازدید</div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/60">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.play') مدت ویدیو</div>
                                    <div dir="ltr" class="mt-2 text-right text-xs font-black text-slate-700 dark:text-slate-200">{{ $video->formatted_duration ?: 'نامشخص' }}</div>
                                </div>
                                <a href="#comments" class="rounded-2xl border border-slate-100 bg-slate-50 p-3 transition hover:border-brand-200 hover:bg-brand-50 dark:border-slate-800 dark:bg-slate-950/60 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">
                                    <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.comment-01') دیدگاه‌ها</div>
                                    <div class="mt-2 text-xs font-black text-slate-700 dark:text-slate-200">{{ number_format($video->approved_comments_count) }} دیدگاه</div>
                                </a>
                            </div>
                        </div>
                    </section>

                    @if($description || $video->tags->isNotEmpty())
                        <x-front.cart class="rounded-[1.4rem] p-5 sm:rounded-[1.75rem] sm:p-7">
                            <x-front.cart-title title="درباره این ویدیو" subtitle="توضیحات و اطلاعات تکمیلی" iconName="right-to-left-list-bullet" />

                            @if($description)
                                <div class="tinymce-content text-sm leading-8 text-slate-700 dark:text-slate-200 dark:prose-invert">
                                    {!! $description !!}
                                </div>
                            @endif

                            @if($video->tags->isNotEmpty())
                                <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
                                    @foreach($video->tags as $tag)
                                        <a href="{{ route('videos.tags', $tag->path) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-[10px] font-black text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300"><span class="text-brand-500">#</span>{{ $tag->title }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </x-front.cart>
                    @endif

                    <section id="comments" class="scroll-mt-28 space-y-6">
                        @if($video->commentsEnabled())
                            <livewire:front.discussion.send-comment-form
                                :commentable="$video"
                                :key="'video-send-comment-form-'.$video->getKey()"
                            />
                        @else
                            <x-front.cart>
                                <div class="flex items-start gap-3">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-5 [&_svg]:w-5">@include('icons.comment-01')</span>
                                    <div>
                                        <h2 class="text-sm font-black text-slate-800 dark:text-slate-200">ارسال دیدگاه غیرفعال است</h2>
                                        <p class="mt-1 text-xs font-medium leading-6 text-slate-500 dark:text-slate-400">در حال حاضر امکان ثبت دیدگاه جدید برای این ویدیو وجود ندارد.</p>
                                    </div>
                                </div>
                            </x-front.cart>
                        @endif

                        @livewire('front.discussion.comments-list', [
                            'commentable' => $video,
                            'key' => 'video-comments-list-'.$video->getKey(),
                        ])
                    </section>
                </div>

                <div data-smart-sidebar-column class="min-w-0 lg:col-span-3">
                    <aside data-smart-sidebar-box class="space-y-5">
                        @if($relatedVideos->isNotEmpty())
                            <section class="rounded-[1.4rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-9 w-9 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.play')</span>
                                        <div>
                                            <h2 class="text-sm font-black text-slate-900 dark:text-slate-100">ویدیوهای مرتبط</h2>
                                            <p class="mt-1 text-[10px] font-bold text-slate-400">پیشنهادهای بعدی برای تماشا</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('videos.index') }}" class="shrink-0 text-[10px] font-black text-brand-700 transition hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200">آرشیو ←</a>
                                </div>

                                <div class="mt-4 space-y-2">
                                    @foreach($relatedVideos as $relatedVideo)
                                        <a href="{{ $relatedVideo->url }}"
                                           class="group flex gap-3 rounded-2xl border border-transparent p-2 transition hover:border-slate-200 hover:bg-slate-50 dark:hover:border-slate-700 dark:hover:bg-slate-800/70">
                                            <div class="relative aspect-video w-28 shrink-0 overflow-hidden rounded-xl bg-slate-950 sm:w-32 lg:w-28 xl:w-32">
                                                @if($relatedVideo->cover?->url)
                                                    <x-front.responsive-image
                                                        :media="$relatedVideo->cover"
                                                        :fallback="$relatedVideo->cover->url"
                                                        :alt="$relatedVideo->title"
                                                        variant="medium"
                                                        class="h-full w-full object-cover"
                                                        wrapper-class="absolute inset-0 h-full w-full transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-105"
                                                        sizes="160px"
                                                    />
                                                @else
                                                    <div class="absolute inset-0 bg-gradient-to-br from-brand-500/40 to-slate-950"></div>
                                                @endif
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                                @if($relatedVideo->formatted_duration)
                                                    <span dir="ltr" class="absolute bottom-1.5 left-1.5 rounded-lg bg-black/70 px-1.5 py-0.5 text-[8px] font-black text-white">{{ $relatedVideo->formatted_duration }}</span>
                                                @endif
                                                <span class="absolute inset-0 m-auto grid h-8 w-8 place-items-center rounded-full bg-brand-500/90 text-white opacity-0 shadow-lg transition group-hover:opacity-100 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.play')</span>
                                            </div>
                                            <div class="min-w-0 flex-1 py-0.5">
                                                <h3 class="line-clamp-2 text-xs font-black leading-6 text-slate-800 transition group-hover:text-brand-700 dark:text-slate-200 dark:group-hover:text-brand-300">{{ $relatedVideo->title }}</h3>
                                                <div class="mt-1.5 flex items-center gap-2 text-[9px] font-bold text-slate-400">
                                                    <span>{{ approximate_view(public_page_views($relatedVideo)) }} بازدید</span>
                                                    @if($relatedVideo->published_at)
                                                        <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                                        <span>{{ jalali_date($relatedVideo->published_at, 'Y/m/d') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if($sidebarCategories->isNotEmpty())
                            <section class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.folder-library')</span>
                                    <div>
                                        <h2 class="text-sm font-black text-slate-900 dark:text-slate-100">دسته‌بندی‌های ویدیو</h2>
                                        <p class="mt-1 text-[10px] font-bold text-slate-400">موضوع مورد علاقه‌تان را انتخاب کنید</p>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-2">
                                    @foreach($sidebarCategories as $sidebarCategory)
                                        @php
                                            $isCurrentCategory = $video->categories->contains('id', $sidebarCategory->id);
                                        @endphp
                                        <a href="{{ route('videos.categories', $sidebarCategory->path) }}"
                                           class="flex items-center justify-between gap-3 rounded-2xl border p-3 transition {{ $isCurrentCategory ? 'border-brand-200 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10' : 'border-transparent bg-slate-50 hover:border-slate-200 hover:bg-white hover:shadow-sm dark:bg-slate-800/60 dark:hover:border-slate-700 dark:hover:bg-slate-800' }}">
                                            <span class="truncate text-xs font-black {{ $isCurrentCategory ? 'text-brand-800 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200' }}">{{ $sidebarCategory->title }}</span>
                                            <span class="shrink-0 rounded-lg bg-white px-2 py-1 text-[9px] font-black text-slate-500 shadow-sm dark:bg-slate-950 dark:text-slate-400">{{ number_format($sidebarCategory->videos_count) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="relative overflow-hidden rounded-[1.4rem] border border-slate-800 bg-slate-950 p-5 shadow-xl shadow-slate-950/10 dark:border-white/10">
                            <div class="absolute -left-12 -top-12 h-36 w-36 rounded-full bg-brand-500/20 blur-3xl"></div>
                            <div class="absolute -bottom-12 -right-12 h-36 w-36 rounded-full bg-violet-500/15 blur-3xl"></div>
                            <div class="relative">
                                <span class="grid h-11 w-11 place-items-center rounded-2xl border border-white/10 bg-white/10 text-brand-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.notification-01')</span>
                                <h2 class="mt-4 text-base font-black leading-7 text-white">{{ $followTitle }}</h2>
                                <p class="mt-2 text-xs font-medium leading-7 text-slate-300">{{ $followDescription }}</p>
                                <div class="mt-5 flex flex-wrap gap-2.5">
                                    @if($followUrl)
                                        <a href="{{ $followUrl }}"
                                           @if(str_starts_with($followUrl, 'http')) target="_blank" rel="noopener noreferrer" @endif
                                           class="rounded-2xl bg-brand-500 px-4 py-2.5 text-xs font-black text-white transition hover:bg-brand-600">{{ $followButtonTitle }}</a>
                                    @endif
                                    <a href="{{ route('videos.index') }}" class="rounded-2xl border border-white/10 bg-white/10 px-4 py-2.5 text-xs font-black text-white transition hover:bg-white/15">مشاهده آرشیو</a>
                                </div>
                            </div>
                        </section>

                        @if($video->tags->isNotEmpty())
                            <section class="rounded-[1.4rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">@include('icons.tag-02')</span>
                                    <h2 class="text-sm font-black text-slate-900 dark:text-slate-100">برچسب‌های این ویدیو</h2>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($video->tags as $tag)
                                        <a href="{{ route('videos.tags', $tag->path) }}"
                                           class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[10px] font-black text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">#{{ $tag->title }}</a>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </aside>
                </div>
            </div>
        </div>
    </main>

    @include('front.videos.partials.detail.share-modal')
@endsection
