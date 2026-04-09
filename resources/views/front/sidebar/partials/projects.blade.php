@php
    $projects = \App\Domain\Project\Models\Project::whereIn('id', collect(get_setting('sidebarWidgets', 'projects_items'))
        ->pluck('project_id')
        ->toArray())
        ->get();
@endphp

<x-front.sidebar.sidebar-item
    :title="get_setting('sidebarWidgets', 'projects_title')"
    :subtitle="get_setting('sidebarWidgets', 'projects_subtitle')"
    iconName="folder-library"
>
    <div class="space-y-3">
        @foreach(get_setting('sidebarWidgets', 'projects_items') ?? [] as $projectItem)
            @php
                $project = $projects->find($projectItem['project_id']);
                if(!$project) continue;
            @endphp
            <div class="rounded-2xl border border-slate-200 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold truncate">
                            <a href="{{ route('projects.detail', $project) }}">
                                {{ $project->title }}
                            </a>
                        </div>
                        <div class="mt-1 text-xs text-slate-500 truncate">{{ $project->excerpt }}</div>
                    </div>
                    <span
                        class="text-[11px] font-extrabold border @if($project->progress_percent == 100) text-green-800 bg-green-50 border-green-100 @else text-brand-800 bg-brand-50 border-brand-100 @endif px-2 py-1 rounded-xl">
                        {{ $project->progress_percent . '%' }}
                    </span>
                </div>
                <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full @if($project->progress_percent == 100) bg-green-500 @else bg-brand-500 @endif"
                         style="width: {{ $project->progress_percent . '%' }}"></div>
                </div>
            </div>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
