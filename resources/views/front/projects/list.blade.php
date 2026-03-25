@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="relative overflow-hidden rounded-xl2 border border-slate-200 bg-slate-900 shadow-soft2">
            <div
                class="absolute inset-0 opacity-90 bg-[radial-gradient(700px_circle_at_15%_20%,rgba(249,115,22,.35),transparent_55%),radial-gradient(700px_circle_at_85%_60%,rgba(59,130,246,.14),transparent_60%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/70 via-slate-950/40 to-slate-950/10"></div>

            <div class="relative p-6 sm:p-10">
                <nav class="text-xs text-white/70 flex flex-wrap gap-2">
                    <a href="/" class="hover:text-brand-300 font-bold transition">خانه</a>
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
                            <div class="mt-2 text-2xl font-black text-white"></div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">درحال توسعه</div>
                            <div class="mt-2 text-2xl font-black text-white"></div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">تکمیل</div>
                            <div class="mt-2 text-2xl font-black text-white"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <livewire:front.project.projects-list/>
    </main>
@endsection

