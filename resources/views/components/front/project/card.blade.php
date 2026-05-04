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

            <div class="mt-3">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>پیشرفت</span>
                    <span class="font-extrabold text-slate-700 dark:text-slate-200">
                        {{ $project->progress_percent }}%
                    </span>
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
