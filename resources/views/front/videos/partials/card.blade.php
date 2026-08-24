<article class="group relative overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-300/70 hover:shadow-xl hover:shadow-brand-500/10 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:border-brand-500/30">
    <a href="{{ $video->url }}" class="relative block aspect-video overflow-hidden bg-slate-950">
        @if($video->cover?->url)
            <x-front.responsive-image
                :media="$video->cover"
                :fallback="$video->cover->url"
                :alt="$video->title"
                variant="medium_large"
                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                wrapper-class="absolute inset-0 h-full w-full"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 440px"
            />
        @else
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_25%,rgba(249,115,22,.32),transparent_42%),radial-gradient(circle_at_80%_75%,rgba(124,58,237,.24),transparent_38%),linear-gradient(135deg,#1e293b,#020617)]"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent"></div>

        <div class="absolute inset-x-3 top-3 flex items-start justify-between gap-2">
            <span class="rounded-full border border-white/15 bg-black/35 px-2.5 py-1 text-[9px] font-black text-white backdrop-blur-md">{{ $video->source_type->label() }}</span>
            @if($video->formatted_duration)
                <span dir="ltr" class="rounded-lg bg-black/65 px-2 py-1 text-[9px] font-black text-white backdrop-blur-sm">{{ $video->formatted_duration }}</span>
            @endif
        </div>

        <span class="absolute inset-0 m-auto grid h-14 w-14 place-items-center rounded-[1rem] border border-white/25 bg-white/15 text-white shadow-xl backdrop-blur-md transition duration-300 group-hover:scale-110 group-hover:border-brand-400 group-hover:bg-brand-500 [&_svg]:h-5 [&_svg]:w-5">@include('icons.play')</span>

        <div class="absolute inset-x-4 bottom-3 flex items-center justify-between text-[9px] font-bold text-white/70">
            <span>{{ number_format(page_views($video)) }} بازدید</span>
            <span>{{ jalali_date($video->published_at, 'Y/m/d') }}</span>
        </div>
    </a>

    <div class="p-4 sm:p-5">
        <div class="mb-3 flex min-h-5 flex-wrap items-center gap-2">
            @forelse($video->categories->take(2) as $category)
                <a href="{{ route('videos.categories', $category->path) }}" class="rounded-lg bg-brand-50 px-2 py-1 text-[9px] font-black text-brand-700 transition hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20">{{ $category->title }}</a>
            @empty
                <span class="text-[9px] font-bold text-slate-400">مودینگ‌وی</span>
            @endforelse
        </div>

        <h2 class="line-clamp-2 min-h-14 text-sm font-black leading-7 text-slate-900 transition group-hover:text-brand-600 sm:text-[15px] dark:text-white dark:group-hover:text-brand-300">
            <a href="{{ $video->url }}">{{ $video->title }}</a>
        </h2>

        @if(filled($video->excerpt))
            <p class="mt-2 line-clamp-2 min-h-12 text-xs font-medium leading-6 text-slate-500 dark:text-slate-400">{{ strip_tags($video->excerpt) }}</p>
        @endif

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4 text-[10px] font-bold text-slate-400 dark:border-slate-800">
            <span class="inline-flex items-center gap-1.5 [&_svg]:h-3.5 [&_svg]:w-3.5">@include('icons.comment-01') {{ number_format($video->approved_comments_count) }} دیدگاه</span>
            <a href="{{ $video->url }}" class="inline-flex items-center gap-1 font-black text-brand-600 transition group-hover:gap-2 dark:text-brand-300">تماشا <span>←</span></a>
        </div>
    </div>
</article>
