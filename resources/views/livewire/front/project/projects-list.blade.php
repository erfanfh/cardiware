<div>
    <section class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="relative flex-1">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                           stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.3-4.3"></path>
                      </svg>
                    </span>
                <input wire:model.live="search"
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 pr-10 pl-4 py-3 text-sm outline-none
                    focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                       placeholder="جستجو بین پروژه‌ها (مثلاً: پچ، فارسی‌ساز، installer…)"/>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="noFilter"
                        @if($filter == 'all')
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    همه
                </button>
                <button wire:click="activeFilter"
                        @if($filter == 'active')
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    فعال
                </button>
                <button wire:click="inactiveFilter"
                        @if($filter == 'inactive')
                            class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold cursor-pointer"
                        @else
                            class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 font-extrabold transition cursor-pointer"
                    @endif
                >
                    تکمیل
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">مرتب‌سازی:</span>
                <select wire:model.live="sort" class="h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none
                 focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                    <option value="most-progress">پیشرفت (بیشتر)</option>
                    <option value="least-progress">پیشرفت (کمتر)</option>
                    <option value="later-update">آپدیت (جدیدتر)</option>
                    <option value="older-update">آپدیت (قدیمی‌تر)</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button @click="toggleTag('Patch')"
                    class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                    :class="tags.includes('Patch') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                Patch
            </button>
            <button @click="toggleTag('Persian')"
                    class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                    :class="tags.includes('Persian') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                Persian
            </button>
            <button @click="toggleTag('Installer')"
                    class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                    :class="tags.includes('Installer') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                Installer
            </button>
            <button @click="toggleTag('Performance')"
                    class="text-[11px] font-extrabold px-2 py-1 rounded-xl border transition"
                    :class="tags.includes('Performance') ? 'text-brand-800 bg-brand-50 border-brand-100' : 'text-slate-700 bg-slate-100 border-slate-200 hover:bg-slate-50'">
                Performance
            </button>

            <button @click="tags=[]"
                    class="text-[11px] font-extrabold px-2 py-1 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                پاک کردن فیلترها
            </button>
        </div>
    </section>

    <section class="mt-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $project)
                <a href="{{ route('projects.detail', $project) }}"
                   class="group rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm hover:shadow-soft transition relative overflow-hidden">
                    <div
                        class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] opacity-70"></div>
                    <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500 opacity-90"></div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div
                                class="text-sm font-extrabold truncate group-hover:text-brand-700 transition">{{ $project->title }}</div>
                            <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">{{ $project->body }}</div>
                        </div>

                        @if($project->status == \App\Domain\Project\Enums\ProjectStatusEnum::ACTIVE)
                            <span
                                class="text-[11px] font-extrabold px-2 py-1 rounded-xl border text-emerald-700 bg-emerald-50 border-emerald-200">{{ $project->status->label() }}</span>
                        @else
                            <span
                                class="text-[11px] font-extrabold px-2 py-1 rounded-xl border text-slate-700 bg-slate-100 border-slate-200">{{ $project->status->label() }}</span>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>پیشرفت</span>
                            <span class="font-extrabold text-slate-700">{{ $project->progress_percent . '%' }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div
                                class="h-full @if($project->status == \App\Domain\Project\Enums\ProjectStatusEnum::ACTIVE) bg-emerald-500 @else bg-brand-500 @endif "
                                style="width: {{ $project->progress_percent . '%' }}"></div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div
                            class="text-xs text-slate-500">{{ 'آخرین آپدیت: ' . jalali_date($project->last_updated_at, 'Y/m/d') }}</div>
                        <span
                            class="text-sm font-extrabold text-brand-700 group-hover:text-brand-800 transition inline-flex items-center gap-2">
                                  جزئیات <span class="font-black">←</span>
                                </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($project->tags as $tag)
                            <div>
                                      <span
                                          class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">{{ $tag->title }}</span>
                            </div>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>

        @if($projects->isEmpty())
            <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="text-base font-extrabold">نتیجه‌ای پیدا نشد</div>
                <div class="mt-2 text-sm text-slate-500">فیلترها را تغییر بده یا عبارت دیگری جستجو کن.</div>
                <button
                    class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                    پاک کردن همه فیلترها
                </button>
            </div>
        @endif
    </section>

    {{ $projects->links() }}
</div>
