@php
    $sidebarProjectsWidget = app(\App\Application\System\Actions\Sidebar\GetSidebarProjectsAction::class)->execute();
@endphp

<x-front.sidebar.sidebar-item
    :title="$sidebarProjectsWidget['title']"
    :subtitle="$sidebarProjectsWidget['subtitle']"
    iconName="folder-library"
>
    <div class="space-y-3">
        @foreach($sidebarProjectsWidget['projects'] as $project)
            <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold truncate dark:text-slate-100">
                            <a href="{{ front_route('projects.detail', $project) }}">
                                {{ $project->title }}
                            </a>
                        </div>

                        <div class="mt-1 text-xs text-slate-500 truncate dark:text-slate-400">
                            {{ $project->excerpt }}
                        </div>
                    </div>

                    <span
                        class="text-[11px] font-extrabold border @if($project->progress_percent == 100) text-green-800 bg-green-50 border-green-100 dark:text-green-300 dark:bg-green-500/10 dark:border-green-500/20 @else text-brand-800 bg-brand-50 border-brand-100 dark:text-brand-300 dark:bg-brand-500/10 dark:border-brand-500/20 @endif px-2 py-1 rounded-xl">
                        {{ $project->progress_percent . '%' }}
                    </span>
                </div>

                <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                    <div class="h-full @if($project->progress_percent == 100) bg-green-500 @else bg-brand-500 @endif"
                         style="width: {{ $project->progress_percent . '%' }}"></div>
                </div>
            </div>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
