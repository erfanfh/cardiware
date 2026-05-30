@props(['project'])

<article x-data
         class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm hover:shadow-lg transition dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20 dark:hover:shadow-black/30">

    <div
        class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>

    <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

    <div class="flex items-center gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm sm:text-base font-extrabold truncate dark:text-slate-100">
                        {{ $project->title }}
                    </div>

                    <div class="text-xs text-slate-500 mt-2 truncate dark:text-slate-400">
                        {{ $project->excerpt() }}
                    </div>
                </div>
            </div>

            @php
                $progress = max(0, min(100, (int) ($project->progress_percent ?? 0)));

                $progressColor = match (true) {
                    $progress >= 100 => 'from-emerald-400 via-emerald-500 to-emerald-600',
                    $progress >= 70 => 'from-cyan-400 via-sky-500 to-blue-600',
                    $progress >= 40 => 'from-amber-300 via-yellow-400 to-orange-400',
                    $progress >= 1 => 'from-rose-400 via-red-500 to-orange-500',
                    default => 'from-slate-300 via-slate-300 to-slate-400 dark:from-slate-600 dark:via-slate-600 dark:to-slate-700',
                };

                $progressIconColor = match (true) {
                    $progress >= 100 => 'text-emerald-600 dark:text-emerald-400',
                    $progress >= 70 => 'text-sky-600 dark:text-sky-400',
                    $progress >= 40 => 'text-amber-600 dark:text-amber-400',
                    $progress >= 1 => 'text-rose-600 dark:text-rose-400',
                    default => 'text-slate-500 dark:text-slate-400',
                };
            @endphp

            <div class="mt-3">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>پیشرفت</span>

                    <span class="font-extrabold text-slate-700 dark:text-slate-200">
                        {{ $progress }}%
                    </span>
                </div>

                <div class="relative h-7">
                    <div
                        class="absolute inset-x-0 top-1/2 h-3 -translate-y-1/2 rounded-full bg-slate-100 ring-1 ring-slate-200/70 dark:bg-slate-800 dark:ring-slate-700/70">
                        <div
                            class="absolute inset-y-0 right-0 overflow-hidden rounded-full bg-gradient-to-l {{ $progressColor }} transition-all duration-700 ease-out"
                            style="width: {{ $progress }}%;"
                        >
                            @if($progress > 0)
                                <div class="project-progress-flow absolute inset-0 opacity-80"></div>

                                <div
                                    class="absolute inset-y-0 left-0 w-12 bg-gradient-to-r from-white/40 via-white/20 to-transparent"></div>
                            @endif
                        </div>
                    </div>
                    
                    @if($progress > 0 && $progress < 100)
                        <div
                            class="project-progress-tip absolute top-1/2 z-20"
                            style="right: calc({{ $progress }}% - 25px);"
                        >
                            <div
                                class="relative flex h-6 w-6 items-center justify-center {{ $progressIconColor }} [&_svg]:relative [&_svg]:h-4 [&_svg]:w-4">
                                <div
                                    class="absolute inset-0 rounded-full bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"></div>

                                @include('icons.arrow-left-01')
                            </div>
                        </div>
                    @endif

                    @if($progress >= 100)
                        <div
                            class="project-progress-target absolute left-0 top-1/2 z-20 flex h-7 w-7 items-center justify-center rounded-full border border-emerald-300 bg-white {{ $progressIconColor }} dark:border-emerald-500/50 dark:bg-slate-900 [&_svg]:h-4 [&_svg]:w-4">
                            @include('icons.target-01')
                        </div>
                    @endif
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
