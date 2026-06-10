<div
    x-data="{ open: false }"
    x-on:open-mobile-navigation.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        x-on:click="open = false"
        class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm md:hidden dark:bg-black/60"
    ></div>

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        class="fixed inset-y-0 right-0 z-50 flex w-[min(88vw,380px)] flex-col overflow-hidden rounded-l-[2rem] border-l border-slate-200 bg-white shadow-2xl md:hidden dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30"
    >
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <div class="h-10 w-10 grid place-items-center">
                    <img
                        src="{{ get_setting('header', 'logo')->path }}"
                        alt="{{ get_setting('general', 'title') }}"
                        class="max-h-10 h-auto object-contain"
                    />
                </div>

                <div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                        {{ get_setting('general', 'title') }}
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        {{ get_setting('general', 'subtitle') }}
                    </div>
                </div>
            </div>

            <button
                type="button"
                x-on:click="open = false"
                class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                aria-label="بستن منو"
            >
                ✕
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4">
            @if($items->isNotEmpty())
                <div class="space-y-2">
                    @foreach($items as $item)
                        @include('front.header.navigation.mobile-item', [
                            'item' => $item,
                            'level' => 0,
                        ])
                    @endforeach
                </div>
            @endif
        </div>

        <div class="border-t border-slate-100 p-4 dark:border-slate-800">
            @if(auth()->check())
                <a href="{{ route('account.dashboard') }}"
                   class="flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition dark:hover:bg-brand-400">
                    @include('icons.user')
                    حساب کاربری
                </a>
            @else
                <a href="{{ front_route('auth.index') }}"
                   class="flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition dark:hover:bg-brand-400">
                    @include('icons.login-square-01')
                    ورود / عضویت
                </a>
            @endif
        </div>
    </aside>
</div>
