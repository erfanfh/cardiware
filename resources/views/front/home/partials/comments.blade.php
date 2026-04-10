<section>
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">★</span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'testimonials_title') }}</h3>
        </div>
    </div>

    <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-28 -right-28 h-80 w-80 rounded-full bg-brand-100/45 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-slate-200/55 blur-3xl"></div>
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
        </div>

        <div class="relative p-5">
            <!-- Summary -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div
                        class="h-12 w-12 rounded-2xl bg-white border border-slate-200 grid place-items-center shadow-sm">
                        <div
                            class="text-lg font-black text-slate-900">{{ get_setting('home', 'testimonials_users_avg_scores') }}</div>
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <div class="text-sm font-extrabold text-slate-900">میانگین رضایت کاربران</div>
                        </div>
                        <div class="mt-1 flex items-center gap-1 text-brand-600">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span class="opacity-80">★</span>
                            <span class="text-xs font-bold text-slate-500 mr-2">
                                {{ get_setting('home', 'testimonials_users_avg_scores') }}
                                از 5
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
                @foreach(get_setting('home', 'testimonials_items') as $item)
                    <article
                        class="rounded-2xl border @if($item['special_experience']) border-brand-100 shadow-lg @else border-slate-200 shadow-sm @endif bg-white/70 backdrop-blur-sm p-5 hover:shadow-lg transition overflow-hidden">
                        @if($item['special_experience'])
                            <div
                                class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-700 via-brand-500 to-brand-300"></div>

                            <div
                                class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 h-56 w-56 rounded-full bg-brand-200/45 blur-3xl"></div>

                            <div class="absolute top-4 left-4">
                                <span
                                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-50 border border-brand-100 px-3 py-1 text-xs font-black text-brand-700">
                                  ★ پیشنهاد کاربران
                                </span>
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img class="h-12 w-12 rounded-2xl object-cover border border-slate-200"
                                     src="{{ $item['image']['path'] }}" alt="{{ $item['title'] }}"/>
                                <div class="min-w-0">
                                    <div
                                        class="text-sm font-extrabold text-slate-900 truncate">{{ $item['title'] }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $item['subtitle'] }}</div>
                                </div>
                            </div>

                            <div class="text-slate-300 text-4xl font-black leading-none">“</div>
                        </div>

                        <p class="mt-4 text-sm text-slate-700 leading-7 line-clamp-4">
                            {!! nl2br(htmlspecialchars(e($item['text']))) !!}
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @foreach(explode(',', $item['tags']) as $tag)
                                <span
                                    class="inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700">
                                    {{ trim($tag) }}
                                </span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
