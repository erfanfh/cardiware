@php
    $footerStats = app(\App\Application\System\Actions\Footer\GetFooterStatsAction::class)->execute();
@endphp

<footer dir="rtl" class="bg-white dark:bg-slate-950">
    <div class="h-px w-full bg-gray-200 dark:bg-slate-800"></div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-y-10 gap-x-6 text-center">
                <div class="flex flex-col items-center">
                    @include('icons.cart-check-line-duotone')
                    <div class="mt-4 text-orange-500 font-extrabold text-sm sm:text-base">
                        +{{ number_format($footerStats['orders']) }}
                        سفارش
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    @include('icons.chat-unread-line-duotone')
                    <div class="mt-4 text-orange-500 font-extrabold text-sm sm:text-base">
                        +{{ number_format($footerStats['comments']) }}
                        دیدگاه
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    @include('icons.clipboard-list-line-duotone')
                    <div class="mt-4 text-orange-500 font-extrabold text-sm sm:text-base">
                        +{{ number_format($footerStats['posts']) }}
                        نوشته
                    </div>
                </div>

                <div class="flex flex-col items-center">
                    @include('icons.shop-line-duotone')
                    <div class="mt-4 text-orange-500 font-extrabold text-sm sm:text-base">
                        +{{ number_format($footerStats['products']) }}
                        محصول
                    </div>
                </div>

            </div>
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

        <div class="py-14">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    class="bg-white rounded-3xl p-8 border border-neutral-200 text-center hover:shadow-md transition dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20">
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
                    class="bg-white rounded-3xl p-8 border border-neutral-200 text-center hover:shadow-md transition dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20">
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
                    class="bg-white rounded-3xl p-8 border border-neutral-200 text-center hover:shadow-md transition dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20">
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

    <div class="mx-auto max-w-7xl px-4 py-8">
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

                @if(get_setting('footer', 'column4_image')->path)
                    <a href="{{ route('namad') }}" target="_blank">
                        <x-front.responsive-image
                            :fallback="get_setting('footer', 'column4_image')->path"
                            :alt="get_setting('footer', 'column4_title')"
                            variant="medium"
                            class="h-auto w-full max-w-[180px] object-contain"
                            wrapper-class="inline-block max-w-[180px]"
                            :lazy="true"
                            :async="true"
                            sizes="180px"
                        />
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

        <a href="https://avinoco.ir" target="_blank" rel="sponsored noopener">
            طراحی و توسعه توسط Avino
        </a>
    </div>
</footer>

@include('front.footer.goftino')
