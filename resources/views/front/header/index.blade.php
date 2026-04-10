@include('front.header.announcement')

<header id="header" class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="h-10 w-10 grid place-items-center">
                    <img
                        src="{{ get_setting('header', 'logo')->path }}"
                        alt="{{ get_setting('general', 'title') }}"
                        title="{{ get_setting('general', 'title') }}"
                        class="max-h-11 h-auto object-contain"
                    />
                </div>
                <div class="leading-tight">
                    <div class="font-extrabold text-lg">{{ get_setting('general', 'title') }}</div>
                    <div class="text-xs text-slate-500">{{ get_setting('general', 'subtitle') }}</div>
                </div>
            </a>

            <!-- Search (with live results dropdown) -->
            <div
                class="flex-1 px-2"
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

            <div class="flex items-center gap-2">
                @include('front.header.notifications')

                <button
                    class="cursor-pointer h-10 w-10 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                    aria-label="حالت شب">
                    @include('icons.moon-02')
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

        <!-- Secondary nav -->
        <nav class="mt-4">
            <div class="flex items-center justify-between gap-3">

                <div class="hidden md:flex items-center text-sm font-extrabold text-slate-800">
                    <!-- Products (Mega Menu) -->
                    <div class="relative group flex items-center">
                        <a href="#"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:text-slate-900 hover:bg-slate-50 transition">
                            <span class="text-base">🎮</span>
                            محصولات
                            <svg class="h-4 w-4 text-slate-400 group-hover:text-slate-600 transition"
                                 viewBox="0 0 24 24" fill="none">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </a>

                        <!-- Mega Panel -->
                        <div class="absolute right-0 top-full z-40 mt-3 w-[min(980px,92vw)]
                          opacity-0 invisible translate-y-2 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                          transition duration-200">
                            <div class="rounded-3xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
                                <div class="grid grid-cols-12">

                                    <!-- Left: Categories -->
                                    <div class="col-span-12 lg:col-span-8 p-6">
                                        <div class="flex items-center justify-between mb-5">
                                            <div class="text-sm font-extrabold text-slate-900">دسته‌بندی‌ها</div>
                                            <a href="#"
                                               class="text-xs font-bold text-brand-600 hover:text-brand-700 transition">مشاهده
                                                همه محصولات</a>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            <a href="#"
                                               class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="h-10 w-10 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center">⚙️</span>
                                                    <div>
                                                        <div class="text-sm font-extrabold text-slate-900">پچ‌ها</div>
                                                        <div class="text-xs text-slate-500">آخرین آپدیت‌ها</div>
                                                    </div>
                                                </div>
                                            </a>

                                            <a href="#"
                                               class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="h-10 w-10 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center">🧩</span>
                                                    <div>
                                                        <div class="text-sm font-extrabold text-slate-900">مادها</div>
                                                        <div class="text-xs text-slate-500">گرافیک و گیم‌پلی</div>
                                                    </div>
                                                </div>
                                            </a>

                                            <a href="#"
                                               class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="h-10 w-10 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center">👕</span>
                                                    <div>
                                                        <div class="text-sm font-extrabold text-slate-900">کیت‌ها</div>
                                                        <div class="text-xs text-slate-500">تیم‌ها و لیگ‌ها</div>
                                                    </div>
                                                </div>
                                            </a>

                                            <a href="#"
                                               class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="h-10 w-10 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center">🏟️</span>
                                                    <div>
                                                        <div class="text-sm font-extrabold text-slate-900">استادیوم
                                                        </div>
                                                        <div class="text-xs text-slate-500">چمن، نور، محیط</div>
                                                    </div>
                                                </div>
                                            </a>

                                            <a href="#"
                                               class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="h-10 w-10 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center">🧠</span>
                                                    <div>
                                                        <div class="text-sm font-extrabold text-slate-900">آموزش نصب
                                                        </div>
                                                        <div class="text-xs text-slate-500">قدم‌به‌قدم</div>
                                                    </div>
                                                </div>
                                            </a>

                                            <a href="#"
                                               class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition">
                                                <div class="flex items-center gap-3">
                                                    <span
                                                        class="h-10 w-10 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center">📦</span>
                                                    <div>
                                                        <div class="text-sm font-extrabold text-slate-900">پکیج‌های
                                                            کامل
                                                        </div>
                                                        <div class="text-xs text-slate-500">دانلود یکجا</div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        <div class="mt-6 flex items-center gap-2">
                                            <a href="#"
                                               class="rounded-2xl bg-slate-900 text-white px-4 py-2 text-xs font-extrabold hover:opacity-90 transition">
                                                دانلودهای محبوب
                                            </a>
                                            <a href="#"
                                               class="rounded-2xl bg-slate-100 text-slate-700 px-4 py-2 text-xs font-extrabold hover:bg-slate-200 transition">
                                                جدیدترین‌ها
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Right: Featured -->
                                    <div
                                        class="col-span-12 lg:col-span-4 p-6 bg-slate-50 border-t lg:border-t-0 lg:border-r border-slate-200">
                                        <div class="text-sm font-extrabold text-slate-900 mb-3">پیشنهادی‌ها</div>

                                        <a href="#"
                                           class="block rounded-2xl bg-white p-4 ring-1 ring-slate-200 hover:ring-brand-200 hover:shadow-md transition mb-3">
                                            <div class="text-sm font-extrabold text-slate-900">پچ لیگ ایران (جدید)</div>
                                            <div class="text-xs text-slate-500 mt-1">نسخه جدید + آموزش کامل نصب</div>
                                        </a>

                                        <a href="#"
                                           class="block rounded-2xl bg-white p-4 ring-1 ring-slate-200 hover:ring-brand-200 hover:shadow-md transition">
                                            <div class="text-sm font-extrabold text-slate-900">بهترین مادهای FC</div>
                                            <div class="text-xs text-slate-500 mt-1">گرافیک، چهره‌ها، کیت‌ها</div>
                                        </a>

                                        <div class="mt-4 rounded-2xl bg-white ring-1 ring-slate-200 p-4">
                                            <div class="text-xs font-bold text-slate-500 mb-2">می‌خوای سریع شروع کنی؟
                                            </div>
                                            <a href="#"
                                               class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                                راهنمای نصب مودینگ وی
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                    <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2"
                                                          stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <span class="mx-1 text-slate-300 select-none">|</span>

                    <a href="#"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:text-slate-900 hover:bg-slate-50 transition">
                        <span class="text-base">⚽</span> FIFA / FC
                    </a>

                    <span class="mx-1 text-slate-300 select-none">|</span>

                    <a href="#"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:text-slate-900 hover:bg-slate-50 transition">
                        <span class="text-base">🏟️</span> PES / eFootball
                    </a>

                    <span class="mx-1 text-slate-300 select-none">|</span>

                    <a href="#"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:text-slate-900 hover:bg-slate-50 transition">
                        <span class="text-base">🧾</span> مطالب
                    </a>
                </div>

                <div class="hidden md:flex items-center gap-2 text-sm text-slate-500"></div>
            </div>
        </nav>
    </div>
</header>

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
