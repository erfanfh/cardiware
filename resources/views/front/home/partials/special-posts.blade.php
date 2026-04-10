<section>
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">⬤</span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'special_posts_title') }}</h3>
        </div>
        <a href="#" class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2">
            مشاهده همه <span class="font-black">←</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach(\App\Domain\Content\Models\Post::take(get_setting('home', 'special_posts_posts_counts'))->get() as $post)
            <a href="{{ route('contents.detail', $post) }}"
               class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm hover:shadow-lg transition">
                <div class="h-12 w-12 rounded-2xl bg-brand-50 grid place-items-center border border-brand-100">
                    <img class="h-10 w-10 rounded-xl object-cover" src="{{ $post->picture?->url }}"
                         alt="{{ $post->title }}">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-extrabold truncate">{{ $post->title }}</div>
                    <div class="text-xs text-slate-500">{{ $post->excerpt }}</div>
                </div>
                <span
                    class="shrink-0 text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">
                    {{ jalali_date($post->created_at, 'Y/m/d') }}
                </span>
            </a>
        @endforeach
    </div>
</section>
