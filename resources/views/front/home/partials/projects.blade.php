<section>
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">⬤</span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'projects_title') }}</h3>
        </div>
        <a href="{{ route('projects.list') }}"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2">
            مشاهده همه
            <span class="[&_svg]:w-4 [&_svg]:h-4">
                @include('icons.arrow-left-02')
            </span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @php
            $projects = \App\Domain\Project\Models\Project::whereIn('id', collect(get_setting('home', 'projects_items'))->pluck('project_id')->toArray())->get();
        @endphp
        @foreach(get_setting('home', 'projects_items') ?? [] as $item)
            @php $project = $projects->where('id', $item['project_id'])->first(); @endphp
            <article
                class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm hover:shadow-lg transition">

                <div
                    class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                <div class="flex items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm sm:text-base font-extrabold truncate">{{ $project->title }}</div>
                                <div class="text-xs text-slate-500 mt-2 truncate">{{ $project->excerpt() }}</div>
                            </div>


                        </div>

                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>پیشرفت</span>
                                <span class="font-extrabold text-slate-700">{{ $project->progress_percent }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-brand-500"
                                     style="width: {{ $project->progress_percent . '%' }}"></div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <a href="{{ route('projects.detail', $project) }}"
                               class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-[0.8rem] font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                اطلاعات پروژه
                                <span class="[&_svg]:w-4 [&_svg]:h-4">
                                    @include('icons.arrow-left-02')
                                </span>
                            </a>

                            @if($project->last_updated_at)
                                <span class="text-xs text-slate-500 hidden sm:inline">
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
