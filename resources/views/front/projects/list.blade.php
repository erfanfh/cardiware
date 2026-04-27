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

                        <p class="mt-4 text-white/80 text-sm sm:text-base leading-7">
                            {!! get_setting('projects', 'first_box_subtitle') !!}
                        </p>
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
                        <article
                            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:shadow-lg transition dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30">

                            <div
                                class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
                            <div
                                class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                            <div class="flex items-center gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm sm:text-base font-extrabold truncate dark:text-slate-100">{{ $project->title }}</div>
                                            <div
                                                class="text-xs text-slate-500 truncate mt-1 dark:text-slate-400">{{ $project->excerpt() }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div
                                            class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                            <span>پیشرفت</span>
                                            <span class="font-extrabold text-slate-700 dark:text-slate-200">{{ $project->progress_percent }}%</span>
                                        </div>
                                        <div
                                            class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                                            <div
                                                class="h-full rounded-full transition-all duration-300"
                                                :class="{
                                                    'bg-emerald-500': ({{ $project->progress_percent }} ?? 0) >= 100,
                                                    'bg-sky-500': {{ $project->progress_percent }} >= 70 && ({{ $project->progress_percent }} ?? 0) < 100,
                                                    'bg-yellow-500': ({{ $project->progress_percent }} ?? 0) >= 40 && ({{ $project->progress_percent }} ?? 0) < 70,
                                                    'bg-red-500': ({{ $project->progress_percent }} ?? 0) >= 1 && ({{ $project->progress_percent }} ?? 0) < 40,
                                                    'bg-slate-300 dark:bg-slate-600': ({{ $project->progress_percent }} ?? 0) < 1
                                                }"
                                                style="width: {{ $project->progress_percent . '%' }}"
                                            ></div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between gap-3">
                                        <a href="{{ front_route('projects.detail', $project) }}"
                                           class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-[0.8rem] font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition dark:shadow-black/20 dark:hover:bg-brand-400">
                                            اطلاعات پروژه
                                            <span class="[&_svg]:w-4 [&_svg]:h-4">
                                        @include('icons.arrow-left-02')
                                    </span>
                                        </a>

                                        @if($project->last_updated_at)
                                            <span class="text-xs text-slate-500 hidden sm:inline dark:text-slate-400">
                                        بروزرسانی:
                                        {{ time_ago($project->last_updated_at) }}
                                    </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
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

                @if($projects->hasMorePages())
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                        <x-front.list.pagination :model="$projects"/>
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
