@include('front.header.announcement')

<header id="header"
        class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200 dark:border-slate-800 dark:bg-slate-950/80">
    <div class="mx-auto max-w-7xl px-4 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                class="md:hidden cursor-pointer h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
                x-data
                x-on:click="$dispatch('open-mobile-navigation')"
                aria-label="باز کردن منو"
            >
                @include('icons.menu-01')
            </button>

            <a href="{{ route('home') }}" class="flex min-w-0 flex-1 items-center gap-2 md:flex-none">
                <div class="group/logo relative h-11 w-11 shrink-0 grid place-items-center">
                    {{-- Animated glow --}}
                    <span
                        class="absolute inset-0 rounded-2xl bg-gradient-to-tr from-brand-500 via-orange-300 to-amber-400
                               opacity-25 blur-md animate-[logoPulse_2.6s_ease-in-out_infinite]
                               transition-all duration-500
                               group-hover/logo:opacity-60 group-hover/logo:blur-lg group-hover/logo:scale-125
                               dark:from-brand-500 dark:via-orange-400 dark:to-amber-300 dark:opacity-30">
                    </span>

                    {{-- Rotating border --}}
                    <span
                        class="absolute inset-0 rounded-2xl p-[1px]
                               bg-[conic-gradient(from_0deg,rgba(249,115,22,.15),rgba(251,146,60,.95),rgba(245,158,11,.35),rgba(249,115,22,.15))]
                               animate-[logoSpin_5s_linear_infinite]
                               opacity-70 transition-all duration-500
                               group-hover/logo:opacity-100 group-hover/logo:scale-110">
                    </span>

                    {{-- Logo box --}}
                    <span
                        class="relative z-10 h-10 w-10 rounded-2xl bg-white/90 grid place-items-center
                               shadow-sm ring-1 ring-white/70 overflow-hidden
                               transition-all duration-500 ease-out
                               group-hover/logo:scale-110 group-hover/logo:rotate-[-6deg] group-hover/logo:shadow-xl
                               dark:bg-slate-950/90 dark:ring-slate-700/80">
                        <img
                            src="{{ get_setting('header', 'logo')->path }}"
                            alt="{{ get_setting('general', 'title') }}"
                            title="{{ get_setting('general', 'title') }}"
                            class="max-h-10 h-auto object-contain transition-transform duration-500 ease-out
                                   animate-[logoFloat_3.2s_ease-in-out_infinite]
                                   group-hover/logo:scale-110 group-hover/logo:rotate-[8deg]"
                        />
                    </span>
                </div>
                <div class="min-w-0 leading-tight max-[410px]:hidden">
                    @if(request()->routeIs('home'))
                        <h1 class="truncate text-base font-extrabold sm:text-lg dark:text-slate-100">
                            {{ get_setting('general', 'title') }}
                        </h1>

                        @if(filled(get_setting('general', 'subtitle')))
                            <h2 class="truncate text-xs text-slate-500 max-sm:hidden dark:text-slate-400">
                                {{ get_setting('general', 'subtitle') }}
                            </h2>
                        @endif
                    @else
                        <div class="truncate text-base font-extrabold sm:text-lg dark:text-slate-100">
                            {{ get_setting('general', 'title') }}
                        </div>

                        @if(filled(get_setting('general', 'subtitle')))
                            <div class="truncate text-xs text-slate-500 max-sm:hidden dark:text-slate-400">
                                {{ get_setting('general', 'subtitle') }}
                            </div>
                        @endif
                    @endif
                </div>
            </a>

            <!-- Search (with live results dropdown) -->
            <div
                class="order-3 w-full px-0 md:order-none md:w-auto md:flex-1 md:px-2"
            >
                <label class="relative block" @keydown.escape.window="open=false">
                    <form action="{{ front_route('search') }}" method="get">
                        <span class="sr-only">جستجو</span>

                        <span
                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400 dark:text-slate-500">
                            @include('icons.search-01')
                        </span>
                        <div x-data="typingPlaceholder">
                            <input
                                type="search"
                                name="q"
                                value="{{ request()->input('q') }}"
                                maxlength="120"
                                :placeholder="currentText"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50
                            pr-10 pl-12 py-3 text-sm outline-none
                            focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300
                            transition dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500/60 dark:focus:bg-slate-950 dark:focus:ring-brand-500/10"
                            />
                        </div>

                        <div class="absolute inset-y-0 left-2 flex items-center">
                            <button
                                type="submit"
                                class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-800"
                                aria-label="جستجو"
                            >
                                @include('icons.search-01')
                            </button>
                        </div>
                    </form>
                </label>
            </div>

            <div class="flex shrink-0 items-center gap-2 max-sm:gap-1.5">
                @include('front.header.notifications')

                @if(!request()->routeIs('cart.index'))
                    <livewire:front.commerce.cart.header-cart-dropdown/>
                @endif

                {{-- سوئیچ روز/شب: خورشید و ابر ↔ ماه و ستاره‌ها --}}
                <button
                    x-data="themeSwitcher()"
                    x-on:click="toggle()"
                    :aria-label="dark ? 'حالت روشن' : 'حالت شب'"
                    :title="dark ? 'حالت روشن' : 'حالت شب'"
                    :class="dark ? 'theme-toggle is-dark' : 'theme-toggle'"
                >
                    <span class="tt-stars" aria-hidden="true"></span>
                    <span class="tt-knob" aria-hidden="true">
                        <span class="tt-cloud"></span>
                        <span class="tt-moon"></span>
                    </span>
                </button>

                @if(auth()->check())
                    <a href="{{ route('account.dashboard') }}"
                       class="header-account-action hidden sm:inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white
                     hover:bg-brand-600 active:bg-brand-700 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] dark:bg-brand-500 dark:hover:bg-brand-400 dark:active:bg-brand-500">
                        <span class="header-account-icon" aria-hidden="true">
                            @include('icons.user')
                        </span>
                        <span>حساب کاربری</span>
                    </a>

                    <a href="{{ route('account.dashboard') }}"
                       class="header-account-action sm:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-brand-500 text-white shadow-lg hover:bg-brand-600 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] dark:bg-brand-500 dark:hover:bg-brand-400 dark:shadow-black/20"
                       aria-label="حساب کاربری">
                        <span class="header-account-icon" aria-hidden="true">
                            @include('icons.user')
                        </span>
                    </a>
                @else
                    <a href="{{ front_route('auth.index') }}"
                       class="header-account-action hidden sm:inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white
                     hover:bg-brand-600 active:bg-brand-700 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] dark:bg-brand-500 dark:hover:bg-brand-400 dark:active:bg-brand-500">
                        <span class="header-account-icon" aria-hidden="true">
                            @include('icons.login-square-01')
                        </span>
                        <span>ورود / عضویت</span>
                    </a>

                    <a href="{{ front_route('auth.index') }}"
                       class="header-account-action sm:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-brand-500 text-white shadow-lg hover:bg-brand-600 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] dark:bg-brand-500 dark:hover:bg-brand-400 dark:shadow-black/20"
                       aria-label="ورود / عضویت">
                        <span class="header-account-icon" aria-hidden="true">
                            @include('icons.login-square-01')
                        </span>
                    </a>
                @endif
            </div>
        </div>

        @include('front.header.navigation.desktop', [
            'items' => $headerNavigationItems ?? collect(),
        ])
    </div>
</header>

@include('front.header.navigation.mobile', [
    'items' => $headerNavigationItems ?? collect(),
])
