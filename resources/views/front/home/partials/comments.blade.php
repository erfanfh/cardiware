<section>
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">★</span>
            <h3 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('home', 'testimonials_title') }}</h3>
        </div>
    </div>

    <div
        class="relative rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -top-28 -right-28 h-80 w-80 rounded-full bg-brand-100/45 blur-3xl dark:bg-brand-500/10"></div>
            <div
                class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-slate-200/55 blur-3xl dark:bg-slate-700/20"></div>
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent dark:via-slate-700"></div>
        </div>

        <div class="relative p-5">
            <!-- Summary -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div
                        class="h-12 w-12 rounded-2xl bg-white border border-slate-200 grid place-items-center shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:shadow-black/20">
                        <div
                            class="text-lg font-black text-slate-900 dark:text-slate-100">{{ get_setting('home', 'testimonials_users_avg_scores') }}</div>
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Average satisfaction
                                Users
                            </div>
                        </div>
                        <div class="mt-1 flex items-center gap-1 text-brand-600 dark:text-brand-400">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span class="opacity-80">★</span>
                            <span class="text-xs font-bold text-slate-500 mr-2 dark:text-slate-400">
                                {{ get_setting('home', 'testimonials_users_avg_scores') }}
                                of 5
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
                @foreach(get_setting('home', 'testimonials_items') as $item)
                    <article
                        class="relative rounded-2xl border @if($item['special_experience']) border-brand-100 shadow-lg dark:border-brand-500/20 @else border-slate-200 shadow-sm dark:border-slate-700 @endif bg-white/70 backdrop-blur-sm p-5 hover:shadow-lg transition overflow-hidden dark:bg-slate-950/60 dark:hover:shadow-black/20">
                        @if($item['special_experience'])
                            <div
                                class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-700 via-brand-500 to-brand-300"></div>

                            {{-- Recommendation badge: above card center, separate from avatar --}}
                            <div class="mb-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-50 border border-brand-100 px-2.5 py-1 text-[11px] font-black text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                    ★ User recommendation
                                </span>
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-front.responsive-image
                                    :fallback="$item['image']['path'] ?? null"
                                    :alt="$item['title'] ?? ''"
                                    variant="thumbnail"
                                    class="h-12 w-12 rounded-2xl object-cover border border-slate-200 dark:border-slate-700"
                                    wrapper-class="h-12 w-12 shrink-0 rounded-2xl"
                                    :lazy="true"
                                    :async="true"
                                    sizes="48px"
                                />
                                <div class="min-w-0">
                                    <div
                                        class="text-sm font-extrabold text-slate-900 truncate dark:text-slate-100">{{ $item['title'] }}</div>
                                    <div
                                        class="text-xs text-slate-500 mt-1 dark:text-slate-400">{{ $item['subtitle'] }}</div>
                                </div>
                            </div>

                            <div class="text-slate-300 text-4xl font-black leading-none dark:text-slate-700">“</div>
                        </div>

                        <p class="mt-4 text-sm text-slate-700 leading-7 dark:text-slate-300">
                            {!! nl2br(htmlspecialchars(e($item['text']))) !!}
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @foreach(explode(',', $item['tags']) as $tag)
                                <span
                                    class="inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
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
