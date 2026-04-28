@php
    $projectsBlock = app(\App\Application\System\Actions\Home\GetHomeProjectsBlockAction::class)->execute();
@endphp

<section>
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">⬤</span>

            <h3 class="text-base font-extrabold dark:text-slate-100">
                {{ $projectsBlock['title'] }}
            </h3>
        </div>

        <a href="{{ front_route('projects.list') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
            مشاهده همه
            <span class="[&_svg]:w-4 [&_svg]:h-4">
                @include('icons.arrow-left-02')
            </span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($projectsBlock['items'] as $project)
            <article
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

                            <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                                <div class="h-full bg-brand-500"
                                     style="width: {{ $project->progress_percent }}%"></div>
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
</section>
