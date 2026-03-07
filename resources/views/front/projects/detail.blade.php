@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8" x-data="projectPage()">

        <!-- HERO / COVER -->
        <section class="relative overflow-hidden rounded-xl2 border border-slate-200 bg-slate-900 shadow-soft2">
            <img :src="project.cover" alt="" class="absolute inset-0 h-full w-full object-cover opacity-80"/>
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/75 via-slate-950/35 to-slate-950/10"></div>

            <div class="relative p-6 sm:p-10">
                <nav class="text-xs text-white/70 flex flex-wrap gap-2">
                    <a href="#" class="hover:text-brand-300 font-bold transition">خانه</a>
                    <span>—</span>
                    <a href="#" class="hover:text-brand-300 font-bold transition">پروژه‌ها</a>
                    <span>—</span>
                    <span class="text-white/90 font-semibold" x-text="project.title"></span>
                </nav>

                <div class="mt-4 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                    <div class="max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-extrabold px-2 py-1 rounded-xl border backdrop-blur"
                  :class="project.status==='done'
                    ? 'text-emerald-700 bg-emerald-50/90 border-emerald-200'
                    : (project.status==='active'
                      ? 'text-brand-800 bg-brand-50/90 border-brand-100'
                      : 'text-slate-700 bg-slate-100/90 border-slate-200')"
                  x-text="project.statusLabel"></span>

                            <template x-for="t in project.tags" :key="'tag-'+t">
              <span
                  class="text-[11px] font-extrabold text-white/90 bg-white/10 border border-white/10 px-2 py-1 rounded-xl backdrop-blur"
                  x-text="t"></span>
                            </template>
                        </div>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-black leading-tight text-white"
                            x-text="project.title"></h1>

                        <p class="mt-4 text-white/80 text-sm sm:text-base leading-7" x-text="project.summary"></p>

                        <!-- quick meta -->
                        <div class="mt-6 flex flex-wrap items-center gap-2 text-xs text-white/70">
            <span
                class="inline-flex items-center gap-2 rounded-2xl bg-white/10 border border-white/10 px-3 py-2 backdrop-blur">
              <span class="h-2 w-2 rounded bg-brand-400"></span>
              آخرین آپدیت: <span class="font-extrabold text-white" x-text="project.updated"></span>
            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-2xl bg-white/10 border border-white/10 px-3 py-2 backdrop-blur">
              نسخه فعلی: <span class="font-extrabold text-white" x-text="project.version"></span>
            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-2xl bg-white/10 border border-white/10 px-3 py-2 backdrop-blur">
              هدف انتشار: <span class="font-extrabold text-white" x-text="project.target"></span>
            </span>
                        </div>
                    </div>

                    <!-- progress box -->
                    <div class="w-full lg:w-[420px] rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-5">
                        <div class="flex items-center justify-between">
                            <div class="text-white/80 text-sm font-extrabold">درصد پیشرفت</div>
                            <div class="text-2xl font-black text-white" x-text="project.progress + '%'"></div>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-white/15 overflow-hidden">
                            <div class="h-full"
                                 :class="project.status==='done' ? 'bg-emerald-500' : 'bg-brand-400'"
                                 :style="`width:${project.progress}%`"></div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[11px] font-extrabold text-white/70">تسک‌ها</div>
                                <div class="mt-1 text-white font-black" x-text="project.stats.tasks"></div>
                            </div>
                            <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[11px] font-extrabold text-white/70">باگ‌ها</div>
                                <div class="mt-1 text-white font-black" x-text="project.stats.bugs"></div>
                            </div>
                            <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                                <div class="text-[11px] font-extrabold text-white/70">آپدیت‌ها</div>
                                <div class="mt-1 text-white font-black" x-text="project.stats.updates"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- MAIN GRID -->
        <section class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- CONTENT -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Overview -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm relative overflow-hidden">
                    <div
                        class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] opacity-80"></div>
                    <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500 opacity-90"></div>

                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path
                  d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
          </span>
                        <div>
                            <div class="text-base font-extrabold">معرفی پروژه</div>
                            <div class="mt-1 text-xs text-slate-500">چه چیزی در این پروژه ساخته می‌شود؟</div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-slate-700 leading-7">
                        <p x-text="project.details.p1"></p>
                        <p x-text="project.details.p2"></p>
                    </div>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-extrabold">تمرکز</div>
                            <div class="mt-1 text-xs text-slate-500 leading-6" x-text="project.focus"></div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-extrabold">سازگاری</div>
                            <div class="mt-1 text-xs text-slate-500 leading-6" x-text="project.compat"></div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-extrabold">پلن انتشار</div>
                            <div class="mt-1 text-xs text-slate-500 leading-6" x-text="project.plan"></div>
                        </div>
                    </div>
                </div>

                <!-- Roadmap -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20l9-5-9-5-9 5 9 5z"></path>
                <path d="M12 12l9-5-9-5-9 5 9 5z"></path>
              </svg>
            </span>
                            <div>
                                <div class="text-base font-extrabold">نقشه راه</div>
                                <div class="mt-1 text-xs text-slate-500">مرحله‌ها و وضعیت هر مرحله</div>
                            </div>
                        </div>
                        <span
                            class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
            به‌روزرسانی: <span x-text="project.updated"></span>
          </span>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-for="(m, idx) in project.roadmap" :key="'m-'+idx">
                            <div class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-2xl border border-slate-200 bg-slate-50 grid place-items-center font-black text-slate-700"
                                                x-text="idx+1"></span>
                                            <div class="text-sm font-extrabold truncate" x-text="m.title"></div>
                                        </div>
                                        <div class="mt-2 text-xs text-slate-500 leading-6" x-text="m.desc"></div>
                                    </div>
                                    <span class="text-[11px] font-extrabold px-2 py-1 rounded-xl border"
                                          :class="m.state==='done'
                        ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                        : (m.state==='doing'
                          ? 'text-brand-800 bg-brand-50 border-brand-100'
                          : 'text-slate-700 bg-slate-100 border-slate-200')"
                                          x-text="m.stateLabel"></span>
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>پیشرفت مرحله</span>
                                        <span class="font-extrabold text-slate-700" x-text="m.progress + '%'"></span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full"
                                             :class="m.state==='done' ? 'bg-emerald-500' : 'bg-brand-500'"
                                             :style="`width:${m.progress}%`"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Updates (changelog style) -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 1 1-2.64-6.36"></path>
              <path d="M21 3v6h-6"></path>
            </svg>
          </span>
                        <div>
                            <div class="text-base font-extrabold">آپدیت‌ها و تغییرات</div>
                            <div class="mt-1 text-xs text-slate-500">تازه‌ترین اتفاقات پروژه</div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-for="u in project.updates" :key="u.id">
                            <div
                                class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 hover:bg-slate-50 transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-extrabold" x-text="u.title"></div>
                                        <div class="mt-2 text-sm text-slate-700 leading-7" x-text="u.text"></div>
                                    </div>
                                    <span class="text-xs text-slate-500 shrink-0" x-text="u.time"></span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="b in u.badges" :key="u.id + '-' + b">
                                        <span
                                            class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl"
                                            x-text="b"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- SIDEBAR -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Key info -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 20h9"></path>
              <path d="M12 4h9"></path>
              <path d="M4 9h16"></path>
              <path d="M4 15h16"></path>
            </svg>
          </span>
                        <div>
                            <div class="text-base font-extrabold">جزئیات سریع</div>
                            <div class="mt-1 text-xs text-slate-500">اطلاعات مهم پروژه</div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">وضعیت</span>
                            <span class="font-extrabold"
                                  :class="project.status==='done' ? 'text-emerald-700' : 'text-brand-700'"
                                  x-text="project.statusLabel"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">پیشرفت</span>
                            <span class="font-extrabold" x-text="project.progress + '%'"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">نسخه فعلی</span>
                            <span class="font-extrabold" x-text="project.version"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">سازنده</span>
                            <span class="font-extrabold" x-text="project.owner"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">پلتفرم</span>
                            <span class="font-extrabold" x-text="project.platform"></span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">آخـرین آپدیت</span>
                            <span class="font-extrabold" x-text="project.updated"></span>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <a href="#updates"
                           class="rounded-2xl bg-brand-500 px-4 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 active:bg-brand-700 transition text-center">
                            مشاهده آپدیت‌ها
                        </a>
                        <a href="#roadmap"
                           class="rounded-2xl bg-white border border-slate-200 text-brand-700 px-4 py-3 text-sm font-extrabold hover:bg-brand-50 hover:border-brand-200 transition text-center">
                            نقشه راه
                        </a>
                    </div>
                </div>

                <!-- Subscribe -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-base font-extrabold">دنبال کردن پروژه</div>
                    <div class="mt-2 text-sm text-slate-600 leading-7">
                        اگر این پروژه برات مهمه، آپدیت‌ها رو دنبال کن تا از نسخه‌های جدید جا نمونی.
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button @click="followed = !followed"
                                class="flex-1 rounded-2xl px-4 py-3 text-sm font-extrabold shadow-soft transition"
                                :class="followed ? 'bg-slate-900 text-white hover:opacity-95' : 'bg-brand-500 text-white hover:bg-brand-600 active:bg-brand-700'">
                            <span x-text="followed ? 'دنبال می‌کنم ✓' : 'دنبال کردن'"></span>
                        </button>

                        <button @click="copyText(window.location.href)"
                                class="h-12 w-12 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                                aria-label="کپی لینک">
                            ⧉
                        </button>
                    </div>

                    <div class="mt-3 text-xs text-slate-500">
                        با کلیک روی ⧉ لینک پروژه کپی می‌شود.
                    </div>
                </div>

                <!-- Placeholder -->
                <div class="rounded-xl2 border border-dashed border-slate-300 bg-white p-5 shadow-sm">
                    <div class="font-extrabold text-slate-700">بنر / تبلیغات</div>
                    <div class="mt-2 text-sm text-slate-500">جایگاه پیشنهادی برای بنر مرتبط با بازی</div>
                    <div class="mt-3 text-xs text-slate-500">size: 320×250</div>
                </div>

            </aside>

        </section>

    </main>
@endsection

@push('scripts')
    <script>
        function projectPage() {
            return {
                followed: false,
                project: {
                    title: 'پچ فارسی‌ساز نسخه جدید',
                    summary: 'بهبود فونت و زیرنویس‌ها، اصلاح منوها و سازگاری بهتر با نسخه‌های مختلف بازی. تمرکز روی نصب ساده و تجربه کاربری روان.',
                    status: 'active',
                    statusLabel: 'فعال',
                    progress: 62,
                    updated: '۳ روز پیش',
                    version: 'v0.9.3',
                    target: 'نسخه پایدار v1.0',
                    owner: 'ModdingWay Team',
                    platform: 'PC (Windows)',
                    focus: 'خوانایی متن، زیرنویس، منو و پایداری',
                    compat: 'سازگار با نسخه‌های 1.0 تا 1.2',
                    plan: 'انتشار نسخه‌های کوتاه و منظم',
                    tags: ['Patch', 'Persian'],
                    cover: 'https://picsum.photos/seed/mw-proj-cover/1400/700',
                    stats: {tasks: 48, bugs: 9, updates: 16},
                    details: {
                        p1: 'این پروژه با هدف ارائه یک تجربه فارسی‌سازی استاندارد و پایدار توسعه داده می‌شود. تمرکز اصلی روی خوانایی، هماهنگی UI و کاهش خطاهای رایج پس از نصب است.',
                        p2: 'در نسخه‌های جدید علاوه بر بهبود فونت‌ها، بخشی از ترجمه‌ها بازنویسی شده و مسیر نصب ساده‌تر شده تا کاربران بدون دردسر بتوانند پچ را اعمال کنند.'
                    },
                    roadmap: [
                        {
                            title: 'پایه‌سازی فونت و منوها',
                            desc: 'هماهنگ‌سازی فونت، اندازه‌ها و اصلاح بخش‌های اصلی UI',
                            state: 'done',
                            stateLabel: 'تکمیل',
                            progress: 100
                        },
                        {
                            title: 'بازنویسی ترجمه‌ها',
                            desc: 'یکدست‌سازی ترجمه‌ها + اصلاح جمله‌بندی‌ها',
                            state: 'doing',
                            stateLabel: 'درحال انجام',
                            progress: 58
                        },
                        {
                            title: 'بهینه‌سازی نصب و بکاپ',
                            desc: 'تشخیص مسیر بازی + بکاپ‌گیری از فایل‌های حساس',
                            state: 'todo',
                            stateLabel: 'در صف',
                            progress: 10
                        },
                        {
                            title: 'نسخه پایدار v1.0',
                            desc: 'تست سناریوهای نصب + رفع خطاهای نهایی',
                            state: 'todo',
                            stateLabel: 'در صف',
                            progress: 0
                        },
                    ],
                    updates: [
                        {
                            id: 1,
                            title: 'بهبود فونت‌ها و خوانایی',
                            text: 'فونت جدید اضافه شد و اندازه‌ها برای رزولوشن‌های مختلف تنظیم شدند.',
                            time: '۳ روز پیش',
                            badges: ['UI', 'Readability']
                        },
                        {
                            id: 2,
                            title: 'رفع چند خطای نصب',
                            text: 'اسکریپت نصب اصلاح شد تا روی مسیرهای غیر استاندارد هم درست عمل کند.',
                            time: '۱ هفته پیش',
                            badges: ['Installer', 'Fix']
                        },
                        {
                            id: 3,
                            title: 'بازنویسی بخشی از ترجمه‌ها',
                            text: 'ترجمه منوهای اصلی بازنگری شد و اصطلاحات یکدست شدند.',
                            time: '۲ هفته پیش',
                            badges: ['Persian', 'Update']
                        },
                    ],
                },

                copyText(txt) {
                    navigator.clipboard?.writeText(txt);
                }
            }
        }
    </script>
@endpush
