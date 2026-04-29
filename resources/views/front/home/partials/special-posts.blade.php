<section>
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">⬤</span>
            <h3 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('home', 'special_posts_title') }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach(get_setting('home', 'special_posts_items') as $item)
            <a href="{{ $item['link'] }}"
               class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm hover:shadow-lg transition dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30">
                <div
                    class="h-12 w-12 rounded-2xl bg-brand-50 grid place-items-center border border-brand-100 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <x-front.responsive-image
                        :fallback="$item['image']['path'] ?? null"
                        :alt="$item['title'] ?? ''"
                        :title="$item['title'] ?? null"
                        variant="thumbnail"
                        class="h-10 w-10 rounded-xl object-cover"
                        wrapper-class="h-10 w-10 shrink-0 rounded-xl"
                        :lazy="true"
                        :async="true"
                        sizes="40px"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-extrabold truncate dark:text-slate-100">{{ $item['title'] }}</div>
                    <div
                        class="text-[0.7rem] mt-1 text-slate-500 truncate dark:text-slate-400">{{ $item['short_description']  }}</div>
                </div>
            </a>
        @endforeach
    </div>
</section>
