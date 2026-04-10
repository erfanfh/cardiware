<section>
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">⬤</span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'download_products_title') }}</h3>
        </div>
        <a href="{{ get_setting('home', 'download_products_all_url') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2">
            مشاهده همه <span class="font-black">←</span>
        </a>
    </div>

    <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div
            class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
        <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

        <div class="p-4">

            <div
                class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-3 pr-8 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach(get_setting('home', 'download_products_items') as $item)
                    <article
                        class="snap-start min-w-[40%] rounded-2xl border border-slate-200 bg-white overflow-hidden hover:shadow-lg transition">
                        <div class="relative aspect-[16/9] bg-slate-100">
                            <img class="absolute inset-0 h-full w-full object-cover"
                                 src="{{ $item['image']['path'] }}"
                                 alt="{{ $item['title'] }}"/>
                            <div
                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-white/90 px-3 py-1 text-xs font-extrabold text-slate-900 ring-1 ring-slate-200">
                                <span class="h-2 w-2 rounded-full bg-brand-500"></span> پیشرفت
                                آپدیت: {{ $item['progress_percent'] }}%
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-xl bg-brand-50 border border-brand-100 px-2 py-1 text-[11px] font-bold text-brand-800">
                                  <span class="h-2 w-2 rounded-full bg-brand-500"></span> PC/Laptop
                                </span>
                                <span class="text-xs text-slate-500">PES</span>
                            </div>
                            <h4 class="mt-3 font-extrabold leading-7">{{ $item['title'] }}</h4>
                            <p class="mt-2 text-sm text-slate-600 leading-7">
                                {{ $item['short_description'] }}
                            </p>

                            @if($item['enable_progress_percent'])
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>پیشرفت ساخت/آپدیت</span>
                                        <span
                                            class="font-extrabold text-slate-700">{{ $item['progress_percent'] }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full bg-brand-500"
                                             style="width: {{ $item['progress_percent'] . '%' }}"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-2 flex items-center justify-center gap-2">
                <span class="h-1.5 w-8 rounded-full bg-brand-500"></span>
                <span class="h-1.5 w-2.5 rounded-full bg-slate-200"></span>
                <span class="h-1.5 w-2.5 rounded-full bg-slate-200"></span>
                <span class="h-1.5 w-2.5 rounded-full bg-slate-200"></span>
            </div>
        </div>
    </div>
</section>
