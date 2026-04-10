<div>
    <section class="mt-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $project)
                <article
                    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:shadow-lg transition">

                    <div
                        class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                    <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                    <div class="flex items-center gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div
                                        class="text-sm sm:text-base font-extrabold truncate">{{ $project->title }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $project->excerpt() }}</div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>پیشرفت</span>
                                    <span class="font-extrabold text-slate-700">{{ $project->progress_percent }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-300"
                                        :class="{
                                            'bg-emerald-500': ({{ $project->progress_percent }} ?? 0) >= 100,
                                            'bg-sky-500': {{ $project->progress_percent }} >= 70 && ({{ $project->progress_percent }} ?? 0) < 100,
                                            'bg-yellow-500': ({{ $project->progress_percent }} ?? 0) >= 40 && ({{ $project->progress_percent }} ?? 0) < 70,
                                            'bg-red-500': ({{ $project->progress_percent }} ?? 0) >= 1 && ({{ $project->progress_percent }} ?? 0) < 40,
                                            'bg-slate-300': ({{ $project->progress_percent }} ?? 0) < 1
                                        }"
                                        style="width: {{ $project->progress_percent . '%' }}"
                                    ></div>
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

        @if($projects->isEmpty())
            <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="text-base font-extrabold">نتیجه‌ای پیدا نشد</div>
                <div class="mt-2 text-sm text-slate-500">فیلترها را تغییر بده یا عبارت دیگری جستجو کن.</div>
                <button
                    class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                    پاک کردن همه فیلترها
                </button>
            </div>
        @endif
    </section>

    {{ $projects->links() }}
</div>
