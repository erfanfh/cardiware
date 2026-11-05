@php
    $footerStats = app(\App\Application\System\Actions\Footer\GetFooterStatsAction::class)->execute();
    $column4LightImagePath = get_setting('footer', 'column4_image')->path;
    $column4DarkImagePath = get_setting('footer', 'column4_image_dark')->path;
    $column4LightImagePath ??= $column4DarkImagePath;
    $column4DarkImagePath ??= $column4LightImagePath;
    $footerNewsEnabled = (bool) get_setting('footer', 'news_enabled', 1);
    $footerNews = $footerNewsEnabled
        ? app(\App\Application\System\Actions\Footer\GetFooterNewsAction::class)->execute()
        : [];
    $footerStatsBg = get_setting('footer', 'stats_bg_image')->path;
    $footerStatsAnimated = (bool) get_setting('footer', 'stats_animation_enabled', 1);
    $footerStatsItems = [
        ['label' => 'کاربر ثبت‌نام‌شده', 'value' => $footerStats['users'], 'icon' => 'icons.user-multiple-02'],
        ['label' => 'تیکت پشتیبانی', 'value' => $footerStats['tickets'], 'icon' => 'icons.message-search-02'],
        ['label' => 'نوشته و مود', 'value' => $footerStats['posts'], 'icon' => 'icons.file-02'],
        ['label' => 'محصول در فروشگاه', 'value' => $footerStats['products'], 'icon' => 'icons.shop-line-duotone'],
    ];
@endphp

@php
    $footerBgImage = get_setting('footer', 'background_image')?->path;
    $footerPattern = (string) get_setting('footer', 'background_pattern', 'none');
@endphp

<footer id="footer" dir="rtl" class="mw-footer-glass relative isolate overflow-hidden bg-white dark:bg-slate-950">
    {{-- پس‌زمینه اختیاری + پترن از تنظیمات --}}
    @if($footerBgImage)
        <img src="{{ $footerBgImage }}" alt="" class="mw-footer-bg" loading="lazy">
    @endif

    @if(in_array($footerPattern, ['dots', 'grid', 'diag'], true))
        <span class="mw-footer-pattern is-{{ $footerPattern }}" aria-hidden="true"></span>
    @endif

    <div class="h-px w-full bg-gray-200 dark:bg-slate-800"></div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="py-12">
            <section class="mw-footer-stats {{ $footerStatsAnimated ? 'is-animated' : '' }}">
                @if($footerStatsBg)
                    <img src="{{ $footerStatsBg }}" alt="" class="mw-footer-stats-bg" loading="lazy">
                @endif

                <span class="mw-footer-stats-overlay" aria-hidden="true"></span>

                @if($footerStatsAnimated)
                    <span class="mw-footer-stats-sweep" aria-hidden="true"></span>
                    <span class="mw-footer-stats-sweep mw-footer-stats-sweep-2" aria-hidden="true"></span>
                @endif

                <div class="relative z-10 text-center">
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-xs font-extrabold text-white backdrop-blur">
                        <span class="mw-footer-stats-pulse" aria-hidden="true"></span>
                        مودینگ‌وی در یک نگاه
                    </span>

                    <h2 class="mt-3 text-xl font-black text-white sm:text-2xl">
                        یک <em class="not-italic text-brand-400">جامعه واقعی</em> از گیمرهای فوتبال
                    </h2>
                </div>

                <div class="relative z-10 mt-7 grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                    @foreach($footerStatsItems as $stat)
                        <div class="mw-footer-stat">
                            <span class="mw-footer-stat-icon">
                                @include($stat['icon'])
                            </span>
                            <b>
                                <span class="mw-count-up" data-count="{{ (int) $stat['value'] }}">۰</span><small>+</small>
                            </b>
                            <span>{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="pb-12 border-b border-neutral-200 dark:border-slate-800">
            <div class="flex flex-col md:flex-row items-center md:items-end justify-between gap-6">
                @if(get_setting('footer', 'logo')->path)
                    <div class="shrink-0">
                        <x-front.responsive-image
                            :fallback="get_setting('footer', 'logo')->path"
                            :alt="get_setting('general', 'title')"
                            variant="medium"
                            class="h-16 md:h-20 w-auto object-contain"
                            wrapper-class="inline-block"
                            :lazy="true"
                            :async="true"
                            sizes="(max-width: 768px) 160px, 200px"
                        />
                    </div>
                @endif

                <p class="text-neutral-700 leading-7 text-sm text-center md:text-justify dark:text-slate-300">
                    {{ get_setting('footer', 'about') }}
                </p>
            </div>
        </div>

        <div class="pt-14 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    class="mw-glass-card rounded-3xl p-8 text-center transition duration-300 hover:-translate-y-1">
                    <div class="flex justify-center mb-4">
                        <div class="grid place-items-center h-14 w-14 rounded-full
                        bg-orange-100 ring-1 ring-orange-200 dark:bg-orange-500/10 dark:ring-orange-500/20">
                            <span class="text-orange-500 text-2xl">@include('icons.checkmark-badge-01')</span>
                        </div>
                    </div>

                    <h3 class="text-orange-500 font-extrabold text-lg mb-3">
                        {{ get_setting('footer', 'quality_title') }}
                    </h3>

                    <p class="text-neutral-700 leading-7 text-sm dark:text-slate-300">
                        {!! get_setting('footer', 'quality_text')  !!}
                    </p>
                </div>

                <div
                    class="mw-glass-card rounded-3xl p-8 text-center transition duration-300 hover:-translate-y-1">
                    <div class="flex justify-center mb-4">
                        <div class="grid place-items-center h-14 w-14 rounded-full
                        bg-orange-100 ring-1 ring-orange-200 dark:bg-orange-500/10 dark:ring-orange-500/20">
                            <span class="text-orange-500 text-2xl">@include('icons.shopping-bag-03')</span>
                        </div>
                    </div>

                    <h3 class="text-black font-extrabold text-xl mb-2 dark:text-slate-100">
                        {{ get_setting('footer', 'store_title') }}
                    </h3>

                    <p class="text-neutral-700 text-sm mb-4 dark:text-slate-300">
                        {!! get_setting('footer', 'store_text') !!}
                    </p>

                    <a href="{{ get_setting('footer', 'store_url') }}"
                       class="text-orange-500 font-extrabold text-sm hover:underline">
                        ورود به فروشگاه
                    </a>
                </div>

                <div
                    class="mw-glass-card rounded-3xl p-8 text-center transition duration-300 hover:-translate-y-1">
                    <div class="flex justify-center mb-4">
                        <div class="grid place-items-center h-14 w-14 rounded-full
                        bg-orange-100 ring-1 ring-orange-200 dark:bg-orange-500/10 dark:ring-orange-500/20">
                            <span class="text-orange-500 text-2xl">@include('icons.store-verified-01')</span>
                        </div>
                    </div>

                    <h3 class="text-orange-500 font-extrabold text-lg mb-3">
                        {{ get_setting('footer', 'commitment_title') }}
                    </h3>

                    <p class="text-neutral-700 leading-7 text-sm dark:text-slate-300">
                        {!! get_setting('footer', 'commitment_text') !!}
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="mx-auto max-w-7xl px-4 pb-8">
        @if(count($footerNews) > 0)
            <section data-footer-news
                     aria-label="{{ get_setting('footer', 'news_title', 'تیتر اخبار') ?: 'تیتر اخبار' }}"
                     class="mb-8 rounded-2xl border border-neutral-200 bg-slate-50/80 px-4 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80 sm:px-5">
                <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:gap-5">
                    <div class="flex shrink-0 items-center gap-2.5 sm:max-w-56">
                        <span aria-hidden="true" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                        </span>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            {{ get_setting('footer', 'news_title', 'تیتر اخبار') ?: 'تیتر اخبار' }}
                        </h3>
                    </div>

                    <div class="min-w-0 flex-1" aria-live="off">
                        @foreach($footerNews as $item)
                            <div data-footer-news-slide @if(!$loop->first) hidden @endif>
                                @if($item['url'])
                                    <a href="{{ $item['url'] }}"
                                       class="group inline-flex max-w-full items-start gap-2 text-sm font-semibold leading-7 text-slate-700 transition-colors hover:text-brand-700 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:text-slate-200 dark:hover:text-brand-400">
                                        <span aria-hidden="true" class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                                        <span class="min-w-0 break-words">{{ $item['title'] }}</span>
                                    </a>
                                @else
                                    <p class="flex items-start gap-2 text-sm font-semibold leading-7 text-slate-700 dark:text-slate-200">
                                        <span aria-hidden="true" class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                                        <span class="min-w-0 break-words">{{ $item['title'] }}</span>
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(count($footerNews) > 1)
                        <div class="flex shrink-0 flex-wrap items-center" aria-label="انتخاب خبر">
                            @foreach($footerNews as $item)
                                <button type="button"
                                        data-footer-news-control="{{ $loop->index }}"
                                        aria-label="نمایش خبر {{ $loop->iteration }}"
                                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                        class="group flex h-8 w-6 cursor-pointer items-center justify-center rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">
                                    <span aria-hidden="true"
                                          class="h-2 w-2 rounded-full bg-slate-300 transition-all group-hover:bg-brand-400 group-aria-[current=true]:w-6 group-aria-[current=true]:bg-brand-500 dark:bg-slate-700 dark:group-hover:bg-brand-400 dark:group-aria-[current=true]:bg-brand-400"></span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 items-start">
            <div class="flex flex-col justify-start items-center lg:items-start lg:col-span-4">
                <div class="font-extrabold mb-3 dark:text-slate-100">{{ get_setting('footer', 'column1_title') }}</div>

                @if(!empty(get_setting('footer', 'address_1')))
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="group text-sm inline-flex items-center gap-2 text-gray-500 transition dark:text-slate-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>

                                {{ get_setting('footer', 'address_1') }}
                            </p>
                        </div>
                    </div>
                @endif

                @if(!empty(get_setting('footer', 'address_2')))
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="group text-sm inline-flex items-center gap-2 text-gray-500 transition dark:text-slate-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>

                                {{ get_setting('footer', 'address_2') }}
                            </p>
                        </div>
                    </div>
                @endif

                @if(!empty(get_setting('footer', 'address_3')))
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="group text-sm inline-flex items-center gap-2 text-gray-500 transition dark:text-slate-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>

                                {{ get_setting('footer', 'address_3') }}
                            </p>
                        </div>
                    </div>
                @endif

                @if(!empty(get_setting('footer', 'address_4')))
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="group text-sm inline-flex items-center gap-2 text-gray-500 transition dark:text-slate-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>

                                {{ get_setting('footer', 'address_4') }}
                            </p>
                        </div>
                    </div>
                @endif

                @if(!empty(get_setting('footer', 'phone')))
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="group text-sm inline-flex items-center gap-2 text-gray-500 transition dark:text-slate-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>

                                <a href="tel:{{ get_setting('footer', 'phone') }}">{{ 'شماره تماس: ' . get_setting('footer', 'phone') }}</a>
                            </p>
                        </div>
                    </div>
                @endif

                @if(!empty(get_setting('footer', 'email')))
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <p
                                class="group text-sm inline-flex items-center gap-2 text-gray-500 transition dark:text-slate-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>

                                <a href="mailto:{{ get_setting('footer', 'email') }}">{{ 'ایمیل: ' . get_setting('footer', 'email') }}</a>
                            </p>
                        </div>
                    </div>
                @endif


                <div class="flex gap-4 mt-3 text-slate-700 dark:text-slate-300">
                    @if(get_setting('footer', 'telegram'))
                        <a href="https://t.me/{{ get_setting('footer', 'telegram') }}"
                           class="transition hover:opacity-70">
                            @include('icons.telegram')
                        </a>
                    @endif

                    @if(get_setting('footer', 'instagram'))
                        <a href="https://instagram.com/{{ get_setting('footer', 'instagram') }}"
                           class="transition hover:opacity-70">
                            @include('icons.instagram')
                        </a>
                    @endif

                    @if(get_setting('footer', 'eitaa'))
                        <a href="https://eitaa.com/{{ get_setting('footer', 'eitaa') }}"
                           class="transition hover:opacity-70">
                            @include('icons.eitaa')
                        </a>
                    @endif

                    @if(get_setting('footer', 'bale'))
                        <a href="https://ble.ir/{{ get_setting('footer', 'bale') }}"
                           class="transition hover:opacity-70">
                            @include('icons.bale')
                        </a>
                    @endif

                    @if(get_setting('footer', 'rubika'))
                        <a href="https://rubika.ir/{{ get_setting('footer', 'rubika') }}"
                           class="transition hover:opacity-70">
                            @include('icons.rubika')
                        </a>
                    @endif

                    @if(get_setting('footer', 'soroush'))
                        <a href="https://splus.ir/{{ get_setting('footer', 'soroush') }}"
                           class="transition hover:opacity-70">
                            @include('icons.soroush')
                        </a>
                    @endif

                    @if(get_setting('footer', 'aparat'))
                        <a href="https://aparat.com/{{ get_setting('footer', 'aparat') }}"
                           class="transition hover:opacity-70">
                            @include('icons.aparat')
                        </a>
                    @endif
                </div>

            </div>

            <div class="flex flex-col justify-start items-center lg:items-start lg:col-span-3">
                <div class="font-extrabold mb-3 dark:text-slate-100">{{ get_setting('footer', 'column2_title') }}</div>

                <ul class="grid gap-2 text-sm">
                    @foreach(get_setting('footer', 'column2_items') as $item)
                        <li>
                            <a href="{{ $item['url'] }}"
                               class="group inline-flex items-center gap-2 text-gray-500 font-thin hover:text-brand-600 transition dark:text-slate-400 dark:hover:text-brand-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>
                                <span>{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex flex-col justify-start items-center lg:items-start lg:col-span-3">
                <div class="font-extrabold mb-3 dark:text-slate-100">{{ get_setting('footer', 'column3_title') }}</div>

                <ul class="grid gap-2 text-sm">
                    @foreach(get_setting('footer', 'column3_items') as $item)
                        <li>
                            <a href="{{ $item['url'] }}"
                               class="group inline-flex items-center gap-2 text-gray-500 font-thin hover:text-brand-600 transition dark:text-slate-400 dark:hover:text-brand-400">
                                <span class="w-1 h-1 rounded-full inline-block bg-brand-600"></span>
                                <span>{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="flex flex-col justify-start items-center lg:items-start lg:col-span-2">
                <div class="font-extrabold mb-3 dark:text-slate-100">{{ get_setting('footer', 'column4_title') }}</div>

                @if($column4LightImagePath || $column4DarkImagePath)
                    <a href="{{ route('namad') }}" target="_blank">
                        @if($column4LightImagePath)
                            <x-front.responsive-image
                                :fallback="$column4LightImagePath"
                                :alt="get_setting('footer', 'column4_title')"
                                variant="medium"
                                class="h-auto w-full max-w-[180px] object-contain"
                                wrapper-class="inline-block max-w-[180px] dark:hidden"
                                :lazy="true"
                                :async="true"
                                sizes="180px"
                            />
                        @endif

                        @if($column4DarkImagePath)
                            <x-front.responsive-image
                                :fallback="$column4DarkImagePath"
                                :alt="get_setting('footer', 'column4_title')"
                                variant="medium"
                                class="h-auto w-full max-w-[180px] object-contain"
                                wrapper-class="hidden max-w-[180px] dark:inline-block"
                                :lazy="true"
                                :async="true"
                                sizes="180px"
                            />
                        @endif
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="h-px w-full bg-gray-200 dark:bg-slate-800"></div>

    <div
        class="mx-auto max-w-7xl px-4 text-xs flex flex-wrap gap-3 justify-between text-gray-600 py-3 dark:text-slate-400">
        <div>
            &copy; {{ get_setting('footer', 'copyright') }}
        </div>

        <a href="https://avinoco.ir" target="_blank" rel="nofollow sponsored noopener">
            طراحی و توسعه توسط Avino
        </a>
    </div>

    @push('scripts')
        <script>
            (function () {
                var counters = document.querySelectorAll('.mw-count-up');

                if (!counters.length) {
                    return;
                }

                var faDigits = function (value) {
                    return String(value)
                        .replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; })
                        .replace(/\B(?=(\d{3})+(?!\d))/g, '٬');
                };

                var animate = function (el) {
                    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                    var started = null;
                    var duration = 1600;

                    var step = function (ts) {
                        if (started === null) started = ts;

                        var p = Math.min(1, (ts - started) / duration);
                        var eased = 1 - Math.pow(1 - p, 3);

                        el.textContent = faDigits(Math.round(target * eased));

                        if (p < 1) requestAnimationFrame(step);
                    };

                    requestAnimationFrame(step);
                };

                var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                if (reduced || !('IntersectionObserver' in window)) {
                    counters.forEach(function (el) {
                        el.textContent = faDigits(el.getAttribute('data-count'));
                    });
                    return;
                }

                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            animate(entry.target);
                            io.unobserve(entry.target);
                        }
                    });
                }, {threshold: 0.4});

                counters.forEach(function (el) { io.observe(el); });
            })();
        </script>
    @endpush
</footer>

@include('front.footer.goftino')
