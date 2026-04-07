@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="relative overflow-hidden rounded-xl2 border border-slate-200 bg-slate-900 shadow-xl">
            <div
                class="absolute inset-0 opacity-90" style='background-image: url("{{ get_setting('projects', 'first_box_image')?->path }}")'></div>
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
                            {{ get_setting('projects', 'first_box_label') }}
                        </div>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-black leading-tight text-white">
                            {{ get_setting('projects', 'first_box_title') }}
                        </h1>

                        <p class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                            {!! get_setting('projects', 'first_box_subtitle') !!}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 w-full lg:w-[420px]">
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">شروع نشده</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $stats->not_started }}</div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">در حال تکمیل</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $stats->in_progress }}</div>
                        </div>
                        <div class="rounded-3xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">تکمیل</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $stats->completed }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <livewire:front.project.projects-list/>
    </main>
@endsection

