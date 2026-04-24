<section>
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">⬤</span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'special_posts_title') }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach(get_setting('home', 'special_posts_items') as $item)
            <a href="{{ $item['link'] }}"
               class="w-full flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm hover:shadow-lg transition">
                <div class="h-12 w-12 rounded-2xl bg-brand-50 grid place-items-center border border-brand-100">
                    <img class="h-10 w-10 rounded-xl object-cover" src="{{ $item['image']['path'] }}"
                         alt="{{ $item['title'] }}">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-extrabold truncate">{{ $item['title'] }}</div>
                    <div class="text-[0.7rem] mt-1 text-slate-500">{{ $item['short_description']  }}</div>
                </div>
            </a>
        @endforeach
    </div>
</section>
