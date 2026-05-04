@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8" x-data="">
        <section
            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-xl dark:border-slate-800 dark:shadow-black/30">
            <div
                class="absolute inset-0 opacity-90 bg-cover bg-center"
                style='background-image: url("{{ get_setting('projects', 'first_box_image')?->path }}")'></div>
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
                            class="inline-flex text-xs items-center gap-2 rounded-2xl bg-white/10 px-3 py-2 text-white/90 border border-white/10 backdrop-blur">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                            {{ get_setting('projects', 'first_box_label') }}
                        </div>

                        <h1 class="mt-4 text-2xl sm:text-3xl font-black leading-tight text-white">
                            {{ get_setting('projects', 'first_box_title') }}
                        </h1>

                        <h2 class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                            {!! get_setting('projects', 'first_box_subtitle') !!}
                        </h2>
                    </div>

                    <div class="grid grid-cols-3 gap-3 w-full lg:w-[420px]">
                        <div class="rounded-2xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">شروع نشده</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $stats->not_started }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">در حال تکمیل</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $stats->in_progress }}</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 border border-white/10 backdrop-blur p-4">
                            <div class="text-[11px] font-extrabold text-white/70">تکمیل</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $stats->completed }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div>
            <section class="mt-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($projects as $project)
                        <x-front.project.card :project="$project"/>
                    @endforeach
                </div>

                @if($projects->isEmpty())
                    <div
                        class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8 dark:border-slate-700 dark:bg-slate-900">
                        <div
                            class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-500">
                            @include('icons.inbox')
                        </div>

                        <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">پروژه ای یافت
                            نشد</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            فیلترها را تغییر بده یا عبارت دیگری جستجو کن.
                        </p>
                    </div>
                @endif

                @if($projects->hasPages())
                    <div
                        class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <x-front.list.pagination :model="$projects"/>
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
