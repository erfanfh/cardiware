@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8" x-data="projectsPage()">
        <!-- Hero / Header -->
        <section class="relative overflow-hidden rounded-xl2 border border-slate-200 bg-slate-900 shadow-soft2">
            <div
                class="absolute inset-0 opacity-90 bg-[radial-gradient(700px_circle_at_15%_20%,rgba(249,115,22,.35),transparent_55%),radial-gradient(700px_circle_at_85%_60%,rgba(59,130,246,.14),transparent_60%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/70 via-slate-950/40 to-slate-950/10"></div>

            <div class="relative p-6 sm:p-10">
                <nav class="text-xs text-white/70 flex flex-wrap gap-2">
                    <a href="#" class="hover:text-brand-300 font-bold transition">خانه</a>
                    <span>—</span>
                    <span class="text-white/90 font-semibold">پروژه‌ها</span>
                </nav>

                <div class="mt-4 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                    <div class="max-w-2xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-3 py-2 text-white/90 border border-white/10 backdrop-blur">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                            پروژه‌های مودینگ‌وی
                        </div>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-black leading-tight text-white">
                            لیست پروژه‌ها + درصد پیشرفت + وضعیت هر پروژه
                        </h1>

                        <p class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                            اینجا می‌تونی پروژه‌های فعال، درحال توسعه یا تکمیل‌شده رو ببینی، جزئیاتشون رو دنبال کنی و از
                            آخرین آپدیت‌ها باخبر بشی.
                        </p>
                    </div>

                    <!-- Quick counters -->
                    <div class="grid grid-cols-3 gap-3 w-full lg:w-[420px]">
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">فعال</div>
                            <div class="mt-2 text-2xl font-black text-white" x-text="counts.active"></div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">درحال توسعه</div>
                            <div class="mt-2 text-2xl font-black text-white" x-text="counts.building"></div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">تکمیل</div>
                            <div class="mt-2 text-2xl font-black text-white" x-text="counts.done"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters / Search -->
        <section class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Search -->
                <div class="relative flex-1">
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="M21 21l-4.3-4.3"></path>
          </svg>
        </span>
                    <input
                        x-model="q"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 pr-10 pl-4 py-3 text-sm outline-none
                 focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                        placeholder="جستجو بین پروژه‌ها (مثلاً: پچ، فارسی‌ساز، installer…)"/>
                </div>

                <!-- Status tabs -->
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="status='all'"
                            :class="status==='all' ? 'h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold'
                                : 'h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition'">
                        همه
                    </button>
                    <button @click="status='active'"
                            :class="status==='active' ? 'h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold'
                                  : 'h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition'">
                        فعال
                    </button>
                    <button @click="status='building'"
                            :class="status==='building' ? 'h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold'
                                    : 'h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition'">
                        درحال توسعه
                    </button>
                    <button @click="status='done'"
                            :class="status==='done' ? 'h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold'
                                : 'h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition'">
                        تکمیل
                    </button>
                </div>

                <!-- Sort -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">مرتب‌سازی:</span>
                    <select x-model="sort"
                            class="h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none
                 focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                        <option value="progress_desc">پیشرفت (بیشتر)</option>
                        <option value="progress_asc">پیشرفت (کمتر)</option>
                        <option value="updated_desc">آپدیت (جدیدتر)</option>
                        <option value="updated_asc">آپدیت (قدیمی‌تر)</option>
                    </select>
                </div>
            </div>

            <!-- Quick tags -->
            <div class="mt-4 flex flex-wrap gap-2">
                <button @click="toggleTag('Patch')"
                        class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                        :class="tags.includes('Patch') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                    Patch
                </button>
                <button @click="toggleTag('Persian')"
                        class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                        :class="tags.includes('Persian') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                    Persian
                </button>
                <button @click="toggleTag('Installer')"
                        class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                        :class="tags.includes('Installer') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                    Installer
                </button>
                <button @click="toggleTag('Performance')"
                        class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                        :class="tags.includes('Performance') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                    Performance
                </button>

                <button x-show="tags.length" @click="tags=[]"
                        class="text-[11px] font-extrabold px-2 py-1 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                    پاک کردن فیلترها
                </button>
            </div>
        </section>

        <!-- Projects Grid -->
        <section class="mt-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="p in filtered()" :key="p.id">
                    <a href="#"
                       class="group rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm hover:shadow-soft transition relative overflow-hidden">
                        <!-- blobs -->
                        <div
                            class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] opacity-70"></div>
                        <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500 opacity-90"></div>

                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-extrabold truncate group-hover:text-brand-700 transition"
                                     x-text="p.title"></div>
                                <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2" x-text="p.desc"></div>
                            </div>

                            <span class="text-[11px] font-extrabold px-2 py-1 rounded-xl border"
                                  :class="p.status==='done'
                    ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                    : (p.status==='active'
                      ? 'text-brand-800 bg-brand-50 border-brand-100'
                      : 'text-slate-700 bg-slate-100 border-slate-200')"
                                  x-text="p.statusLabel"></span>
                        </div>

                        <!-- Progress -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>پیشرفت</span>
                                <span class="font-extrabold text-slate-700" x-text="p.progress + '%'"></span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full"
                                     :class="p.status==='done' ? 'bg-emerald-500' : 'bg-brand-500'"
                                     :style="`width:${p.progress}%`"></div>
                            </div>
                        </div>

                        <!-- Meta -->
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-xs text-slate-500" x-text="'آخرین آپدیت: ' + p.updated"></div>
                            <span
                                class="text-sm font-extrabold text-brand-700 group-hover:text-brand-800 transition inline-flex items-center gap-2">
              جزئیات <span class="font-black">←</span>
            </span>
                        </div>

                        <!-- Tags -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            <template x-for="t in p.tags" :key="p.id + '-' + t">
              <span
                  class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl"
                  x-text="t"></span>
                            </template>
                        </div>
                    </a>
                </template>
            </div>

            <!-- Empty state -->
            <div x-show="filtered().length === 0"
                 class="mt-6 rounded-xl2 border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="text-base font-extrabold">نتیجه‌ای پیدا نشد</div>
                <div class="mt-2 text-sm text-slate-500">فیلترها را تغییر بده یا عبارت دیگری جستجو کن.</div>
                <button @click="resetAll()"
                        class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                    پاک کردن همه فیلترها
                </button>
            </div>
        </section>

        <!-- Pagination (visual) -->
        <section class="mt-8 flex items-center justify-center">
            <div class="inline-flex items-center gap-2 rounded-3xl border border-slate-200 bg-white p-2 shadow-sm">
                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
                    →
                </button>
                <button class="h-10 w-10 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold">
                    1
                </button>
                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition font-extrabold">
                    2
                </button>
                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition font-extrabold">
                    3
                </button>
                <span class="px-2 text-xs text-slate-500">…</span>
                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition font-extrabold">
                    9
                </button>
                <button
                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
                    ←
                </button>
            </div>
        </section>
    </main>
@endsection
