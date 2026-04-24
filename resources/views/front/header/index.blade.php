@include('front.header.announcement')

<header id="header" class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                class="md:hidden cursor-pointer h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                x-data
                x-on:click="$dispatch('open-mobile-navigation')"
                aria-label="باز کردن منو"
            >
                @include('icons.menu-01')
            </button>

            <a href="{{ route('home') }}" class="flex min-w-0 flex-1 items-center gap-2 md:flex-none">
                <div class="h-10 w-10 grid place-items-center">
                    <img
                        src="{{ get_setting('header', 'logo')->path }}"
                        alt="{{ get_setting('general', 'title') }}"
                        title="{{ get_setting('general', 'title') }}"
                        class="max-h-11 h-auto object-contain"
                    />
                </div>
                <div class="min-w-0 leading-tight max-[380px]:hidden">
                    <div class="truncate text-base font-extrabold sm:text-lg">
                        {{ get_setting('general', 'title') }}
                    </div>
                    <div class="truncate text-xs text-slate-500 max-sm:hidden">
                        {{ get_setting('general', 'subtitle') }}
                    </div>
                </div>
            </a>

            <!-- Search (with live results dropdown) -->
            <div
                class="order-3 w-full px-0 md:order-none md:w-auto md:flex-1 md:px-2"
                x-data="{
                      q: '',
                      open: false,
                      loading: false,

                      // Demo results (بعداً از API میاد)
                      all: [
                        { type: 'محصول', title: 'پچ لیگ ایران برای FC26', meta: 'FC/PC • 1.3GB', href: '#' },
                        { type: 'مطلب', title: 'آموزش نصب پچ لیگ ایران روی FC26', meta: '۵ دقیقه مطالعه', href: '#' },
                        { type: 'محصول', title: 'PES Graphic Pack 2026', meta: 'PES/PC • 820MB', href: '#' },
                        { type: 'مطلب', title: 'رفع ارورهای رایج نصب مودها', meta: 'راهنما', href: '#' },
                        { type: 'محصول', title: 'آپدیت نقل‌وانتقالات FC26', meta: 'فایل • 120MB', href: '#' },
                      ],

                      get results() {
                        if (!this.q || this.q.trim().length < 2) return [];
                        const s = this.q.trim().toLowerCase();
                        return this.all.filter(i => i.title.toLowerCase().includes(s)).slice(0, 6);
                      },

                      onInput() {
                        this.open = true;
                        this.loading = true;

                        // دمو: شبیه‌سازی سرچ آنلاین
                        clearTimeout(this._t);
                        this._t = setTimeout(() => {
                          this.loading = false;
                        }, 250);
                      },

                      closeSoon() {
                        setTimeout(() => (this.open = false), 120);
                      }
                    }"
            >
                <label class="relative block" @keydown.escape.window="open=false">
                    <form action="{{ route('search') }}" method="get">
                        <span class="sr-only">جستجو</span>

                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                            @include('icons.search-01')
                        </span>
                        <div x-data="typingPlaceholder()">
                            <input
                                type="search"
                                name="q"
                                value="{{ request()->input('q') }}"
                                :placeholder="currentText"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50
                            pr-10 pl-12 py-3 text-sm outline-none
                            focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300
                            transition"
                            />
                        </div>

                        <div class="absolute inset-y-0 left-2 flex items-center">
                            <button
                                type="submit"
                                class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer"
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

                <button
                    x-data="themeSwitcher()"
                    x-on:click="toggle()"
                    x-init="init()"
                    :aria-label="dark ? 'حالت روشن' : 'حالت شب'"
                    class="cursor-pointer h-10 w-10 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800 transition grid place-items-center text-slate-700 dark:text-slate-100"
                >
                    <span x-show="!dark">
                        @include('icons.moon-02')
                    </span>

                    <span x-show="dark" x-cloak>
                        @include('icons.sun-01')
                    </span>
                </button>

                @if(auth()->check())
                    <a href="{{ route('account') }}"
                       class="hidden sm:inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white
                    hover:bg-brand-600 active:bg-brand-700 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem]">
                        @include('icons.user')
                        حساب کاربری
                    </a>

                    <a href="{{ route('account') }}"
                       class="sm:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-brand-500 text-white shadow-lg hover:bg-brand-600 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem]"
                       aria-label="حساب کاربری">
                        @include('icons.user')
                    </a>
                @else
                    <a href="{{ route('auth.index') }}"
                       class="hidden sm:inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white
                    hover:bg-brand-600 active:bg-brand-700 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem]">
                        @include('icons.login-square-01')
                        ورود / عضویت
                    </a>

                    <a href="{{ route('auth.index') }}"
                       class="sm:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-brand-500 text-white shadow-lg hover:bg-brand-600 transition [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem]"
                       aria-label="ورود / عضویت">
                        @include('icons.login-square-01')
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

@push('scripts')
    <script>
        function typingPlaceholder() {
            return {
                texts: [
                    "دنبال چی می‌گردی؟",
                    "دنبال پچ های لیگ ایران هستی؟",
                    "فیس پک تیم های فوتبال رو میخوای؟",
                    "میخوای بازی هاتو آپدیت کنی؟",
                    "دنبال آموزش های فنی هستی؟",
                    "کرک بازی های جدید رو میخوای؟",
                    "تو مودینگ وی همه جیز پیدا میشه!",
                ],
                currentText: "",
                textIndex: 0,
                charIndex: 0,
                typing: true,

                init() {
                    this.loop();
                },

                loop() {
                    let current = this.texts[this.textIndex];

                    if (this.typing) {
                        this.currentText = current.substring(0, this.charIndex++);
                        if (this.charIndex > current.length) {
                            this.typing = false;
                            setTimeout(() => this.loop(), 1200);
                            return;
                        }
                    } else {
                        this.currentText = current.substring(0, this.charIndex--);
                        if (this.charIndex <= 0) {
                            this.typing = true;
                            this.textIndex = (this.textIndex + 1) % this.texts.length;
                        }
                    }

                    setTimeout(() => this.loop(), this.typing ? 70 : 40);
                }
            }
        }
    </script>
@endpush
