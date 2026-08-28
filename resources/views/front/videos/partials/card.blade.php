@php
    $primaryCategory = $video->categories->first();
    $excerpt = trim(strip_tags((string) $video->excerpt));
@endphp

<article
    class="group flex h-full flex-col overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-500/10 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:border-brand-500/30">
    <a href="{{ $video->url }}" class="relative block aspect-video overflow-hidden bg-slate-950"
       aria-label="تماشای {{ $video->title }}">
        @if($video->cover?->url)
            <x-front.responsive-image
                :media="$video->cover"
                :fallback="$video->cover->url"
                :alt="$video->title"
                variant="medium_large"
                class="h-full w-full object-cover"
                wrapper-class="absolute inset-0 h-full w-full transform-gpu will-change-transform transition-transform duration-500 ease-out group-hover:scale-105"
                sizes="(max-width: 640px) 100vw, (max-width: 1280px) 50vw, 300px"
            />
        @else
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_22%_24%,rgba(249,115,22,.34),transparent_36%),radial-gradient(circle_at_82%_76%,rgba(139,92,246,.28),transparent_38%),linear-gradient(135deg,#1e293b,#020617)]"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

        <div class="absolute inset-x-3 top-3 flex items-start justify-between gap-2">
            @if($video->formatted_duration)
                <span dir="ltr"
                      class="rounded-xl border border-white/10 bg-black/65 px-2.5 py-1 text-[10px] font-black text-white backdrop-blur">{{ $video->formatted_duration }}</span>
            @endif
        </div>

        <span
            class="absolute inset-0 m-auto grid h-12 w-12 place-items-center rounded-full bg-brand-600 text-white shadow-xl shadow-brand-950/30 transition duration-300 group-hover:scale-110 group-hover:bg-brand-500 [&_svg]:h-5 [&_svg]:w-5">
            @include('icons.play')
        </span>
    </a>

    <div class="flex grow flex-col p-4">
        <div class="flex min-h-6 flex-wrap items-center gap-2">
            @foreach($video->categories->take(2) as $category)
                <a href="{{ route('videos.categories', $category->path) }}"
                   class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[10px] font-black text-brand-800 transition hover:bg-brand-100 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20">{{ $category->title }}</a>
            @endforeach
            @if(!$primaryCategory)
                <span
                    class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">مودینگ تی‌وی</span>
            @endif
        </div>

        <h2 class="mt-3 line-clamp-2 min-h-14 text-sm font-black leading-7 text-slate-900 transition group-hover:text-brand-700 sm:text-[15px] dark:text-slate-100 dark:group-hover:text-brand-300">
            <a href="{{ $video->url }}">{{ $video->title }}</a>
        </h2>

        @if($excerpt !== '')
            <p class="mt-2 line-clamp-2 min-h-12 text-xs font-medium leading-6 text-slate-600 dark:text-slate-400">{{ $excerpt }}</p>
        @endif

        <div
            class="mt-auto flex items-center justify-between gap-3 pt-4 text-[10px] font-bold text-slate-500 dark:text-slate-400">
            <div class="flex min-w-0 items-center gap-2">
                <span class="shrink-0">{{ jalali_date($video->published_at, 'Y/m/d') }}</span>
                <span class="h-1 w-1 shrink-0 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                <span class="truncate">{{ approximate_view(page_views($video)) }} بازدید</span>
            </div>
            <a href="{{ $video->url }}"
               class="shrink-0 font-black text-brand-700 transition group-hover:text-brand-800 dark:text-brand-300 dark:group-hover:text-brand-200">تماشا
                ←</a>
        </div>
    </div>
</article>
