@include('front.header.announcement')

<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
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
                    <span class="sr-only">جستجو</span>

                    <!-- Search Icon (right) -->
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
      <path
          d="M21 21l-4.3-4.3m1.3-5.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
      />
    </svg>
  </span>

                    <!-- Input -->
                    <input
                        type="search"
                        x-model="q"
                        @input="onInput()"
                        @focus="open = true"
                        @blur="closeSoon()"
                        placeholder="دنبال چی می‌گردی؟ (مثلاً: پچ لیگ ایران، FC26، PES...)"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50
           pr-10 pl-12 py-3 text-sm outline-none
           focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300
           transition"
                    />

                    <!-- Left action icon (submit / open results) -->
                    <div class="absolute inset-y-0 left-2 flex items-center">
                        <button
                            type="button"
                            @click="open = true"
                            class="h-9 w-9 rounded-xl border border-slate-200 bg-white
             hover:bg-slate-50 transition grid place-items-center"
                            aria-label="جستجو"
                        >
                            <!-- Magnifier icon -->
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-slate-700">
                                <path
                                    d="M21 21l-4.3-4.3m1.3-5.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Live results dropdown -->
                    <div
                        x-show="open && (q.trim().length > 0)"
                        x-transition.opacity.duration.120ms
                        class="absolute right-0 left-0 mt-2 z-50"
                        style="display:none"
                    >
                        <div class="rounded-3xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <div class="text-xs font-bold text-slate-500">
                                    <span x-show="q.trim().length < 2">برای نمایش نتایج، حداقل ۲ حرف تایپ کن…</span>
                                    <span x-show="q.trim().length >= 2">نتایج برای: <span class="text-slate-800"
                                                                                          x-text="q"></span></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span x-show="loading" class="text-xs font-bold text-slate-400">در حال جستجو…</span>
                                    <button
                                        type="button"
                                        class="text-xs font-bold text-brand-700 hover:text-brand-800 transition"
                                        @click="open=false"
                                    >
                                        بستن
                                    </button>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="max-h-72 overflow-auto">
                                <!-- Empty -->
                                <div x-show="!loading && q.trim().length >= 2 && results.length === 0"
                                     class="p-4 text-sm text-slate-600">
                                    نتیجه‌ای پیدا نشد.
                                </div>

                                <!-- Results -->
                                <template x-for="item in results" :key="item.title">
                                    <a
                                        :href="item.href"
                                        class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 transition"
                                    >
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                <span
                    class="text-[11px] font-extrabold text-brand-700 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl"
                    x-text="item.type"></span>
                                                <div class="text-sm font-extrabold text-slate-900 truncate"
                                                     x-text="item.title"></div>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500" x-text="item.meta"></div>
                                        </div>
                                        <span class="shrink-0 text-brand-700 font-extrabold">←</span>
                                    </a>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div
                                class="px-4 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                                <div class="text-xs text-slate-500">Enter برای رفتن به صفحه نتایج کامل</div>
                                <a href="#"
                                   class="text-xs font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    مشاهده همه نتایج ←
                                </a>
                            </div>
                        </div>
                    </div>

                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                    aria-label="سبد دانلود">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-slate-700">
                        <path d="M6 7h14l-1.5 12h-11L6 7Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 7a3 3 0 0 1 6 0" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>

                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                    aria-label="اعلان‌ها">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-slate-700">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M13.7 21a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>

                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                    aria-label="حالت شب">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-slate-700">
                        <path d="M21 13.2A7.5 7.5 0 0 1 10.8 3 6.5 6.5 0 1 0 21 13.2Z" stroke="currentColor"
                              stroke-width="2"/>
                    </svg>
                </button>

                <a href="#"
                   class="hidden sm:inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white
                    shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-white">
                        <path d="M15 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h9" stroke="currentColor" stroke-width="2"/>
                        <path d="M10 12h10m0 0-3-3m3 3-3 3" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round"/>
                    </svg>
                    ورود / عضویت
                </a>

                <a href="#"
                   class="sm:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-brand-500 text-white shadow-soft hover:bg-brand-600 transition"
                   aria-label="ورود / عضویت">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-white">
                        <path d="M15 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h9" stroke="currentColor" stroke-width="2"/>
                        <path d="M10 12h10m0 0-3-3m3 3-3 3" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round"/>
                    </svg>
                </a>
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
