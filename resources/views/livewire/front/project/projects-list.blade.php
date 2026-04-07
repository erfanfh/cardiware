<div>
    <section class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="noFilter"
                        @if($filter == \App\Domain\Project\Enums\ProjectFilterEnum::ALL->value)
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    {{ \App\Domain\Project\Enums\ProjectFilterEnum::ALL->label() }}
                </button>
                <button wire:click="notStartedFilter"
                        @if($filter == \App\Domain\Project\Enums\ProjectFilterEnum::NOT_STARTED->value)
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    {{ \App\Domain\Project\Enums\ProjectFilterEnum::NOT_STARTED->label() }}
                </button>
                <button wire:click="inprogressFilter"
                        @if($filter == \App\Domain\Project\Enums\ProjectFilterEnum::INPROGRESS->value)
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    {{ \App\Domain\Project\Enums\ProjectFilterEnum::INPROGRESS->label() }}
                </button>
                <button wire:click="completedFilter"
                        @if($filter == \App\Domain\Project\Enums\ProjectFilterEnum::COMPLETED->value)
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    {{ \App\Domain\Project\Enums\ProjectFilterEnum::COMPLETED->label() }}
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">مرتب‌سازی:</span>
                <select wire:model.live="sort" class="h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none
                 focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                    <option value="{{ \App\Domain\Project\Enums\ProjectSortEnum::MOST_PROGRESS->value }}">
                        {{ \App\Domain\Project\Enums\ProjectSortEnum::MOST_PROGRESS->label() }}
                    </option>
                    <option value="{{ \App\Domain\Project\Enums\ProjectSortEnum::LEAST_PROGRESS->value }}">
                        {{ \App\Domain\Project\Enums\ProjectSortEnum::LEAST_PROGRESS->label() }}
                    </option>
                    <option value="{{ \App\Domain\Project\Enums\ProjectSortEnum::LATER_UPDATE->value }}">
                        {{ \App\Domain\Project\Enums\ProjectSortEnum::LATER_UPDATE->label() }}
                    </option>
                    <option value="{{ \App\Domain\Project\Enums\ProjectSortEnum::OLDER_UPDATE->value }}">
                        {{ \App\Domain\Project\Enums\ProjectSortEnum::OLDER_UPDATE->label() }}
                    </option>
                </select>
            </div>
        </div>
    </section>

    <section class="mt-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $project)
                <article
                    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:shadow-lg transition">

                    <div
                        class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                    <div class="pointer-events-none absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                    <div class="flex items-center gap-4">
                        <div class="shrink-0">
                            <div class="h-12 w-12 rounded-2xl border border-slate-200 bg-white grid place-items-center">
                                <span class="text-xs font-extrabold text-slate-400">SVG</span>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm sm:text-base font-extrabold truncate">{{ $project->title }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $project->excerpt }}</div>
                                </div>


                                <span
                                    class="shrink-0 inline-flex items-center gap-2 rounded-2xl bg-brand-50 border border-brand-100 px-3 py-1.5 text-[11px] font-extrabold text-brand-800">
                                    <span
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-white text-[11px] font-black">%</span>
                                    {{ $project->progress_percent }}٪ تکمیل
                                  </span>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>پیشرفت</span>
                                    <span class="font-extrabold text-slate-700">{{ $project->progress_percent }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-brand-500" style="width: {{ $project->progress_percent . '%' }}"></div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-3">
                                <a href="{{ route('projects.detail', $project) }}"
                                   class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                    اطلاعات پروژه
                                    <span class="font-black" dir="ltr">←</span>
                                </a>

                                <span class="text-xs text-slate-500 hidden sm:inline">
                                    بروزرسانی: {{ jalali_date($project->last_updated_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="hidden sm:block shrink-0">
                            <div
                                class="h-14 w-14 rounded-3xl border border-slate-200 bg-slate-50 grid place-items-center">
                                <span class="text-xs font-extrabold text-slate-400">SVG</span>
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
