@php
    $clubCards = app(\App\Application\System\Actions\Home\GetHomeClubCardsAction::class)->execute();
    $vipUrl = in_array($clubCards['vip']['button_url'], [null, '', '#'], true)
        ? route('vip.index')
        : $clubCards['vip']['button_url'];
@endphp

<section class="mw-club-cards">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">

        {{-- اشتراک ویژه (طلایی) --}}
        <a href="{{ $vipUrl }}" class="mw-club-vip group">
            <span class="mw-club-vip-ring" aria-hidden="true"></span>
            <span class="mw-club-vip-sheen" aria-hidden="true"></span>

            <div class="relative z-10 flex h-full flex-col">
                <div class="flex items-center gap-3">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-amber-300 to-yellow-600 text-white shadow-lg shadow-amber-500/30 [&_svg]:h-6 [&_svg]:w-6">
                        @include('icons.crown-03')
                    </span>
                    <div class="min-w-0">
                        <div class="text-base font-black text-amber-500 dark:text-amber-300">
                            {{ $clubCards['vip']['title'] }}
                        </div>
                        <div class="text-[11px] font-bold text-amber-600/70 dark:text-amber-200/60">
                            دسترسی نامحدود به همه پچ‌ها
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-xs leading-6 text-slate-600 dark:text-slate-300">
                    {{ $clubCards['vip']['subtitle'] }}
                </p>

                <span class="mt-auto pt-4 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-l from-amber-400 to-yellow-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/30 transition group-hover:shadow-amber-500/50 group-hover:brightness-110">
                    {{ $clubCards['vip']['button_title'] }}
                    <span class="[&_svg]:h-4 [&_svg]:w-4">
                        @include('icons.arrow-left-02')
                    </span>
                </span>
            </div>
        </a>

        {{-- پروژه‌ها (کارت ثابت بدون نوار پیشرفت) --}}
        <div class="mw-club-projects">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                        @include('icons.folder-library')
                    </span>
                    <h3 class="text-base font-extrabold dark:text-slate-100">{{ $clubCards['projectsTitle'] }}</h3>
                </div>

                <a href="{{ front_route('projects.list') }}"
                   class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2 dark:text-brand-300 dark:hover:text-brand-200">
                    مشاهده همه
                    <span class="[&_svg]:w-4 [&_svg]:h-4">
                        @include('icons.arrow-left-02')
                    </span>
                </a>
            </div>

            <div class="space-y-2.5">
                @forelse($clubCards['projects'] as $project)
                    @php
                        $projectDone = ((int)($project->progress_percent ?? 0)) >= 100;
                    @endphp
                    <a href="{{ front_route('projects.detail', $project) }}"
                       class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 transition hover:border-brand-200 hover:bg-white dark:border-slate-700/70 dark:bg-slate-900/60 dark:hover:border-brand-500/30 dark:hover:bg-slate-900">
                        <span class="mw-club-projects-dot {{ $projectDone ? 'is-done' : '' }}" aria-hidden="true"></span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-slate-800 transition group-hover:text-brand-700 dark:text-slate-200 dark:group-hover:text-brand-300">
                                {{ $project->title }}
                            </span>
                            <span class="block truncate text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $project->excerpt() }}
                            </span>
                        </span>

                        @if($projectDone)
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10.5px] font-extrabold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mw-pulse-dot"></span>
                                تکمیل شد
                            </span>
                        @else
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-2.5 py-1 text-[10.5px] font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand-500 mw-pulse-dot"></span>
                                در حال ساخت
                            </span>
                        @endif
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 px-4 py-6 text-center text-xs text-slate-500 dark:text-slate-400">
                        پروژه‌ای برای نمایش انتخاب نشده است.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- کاربران برتر هفته (هاله طلایی چرخان) --}}
        <a href="{{ route('loyalty.leaderboard') }}" class="mw-club-top-users group">
            <span class="mw-club-gold-ring" aria-hidden="true"></span>

            <div class="relative z-10 flex h-full flex-col">
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-700/70 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-[1.05rem] [&_svg]:w-[1.05rem]">
                            @include('icons.medal-06')
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold dark:text-slate-100">کاربران برتر هفته</h3>
                            <div class="text-[10.5px] text-slate-500 dark:text-slate-400">فعال‌ترین اعضای باشگاه مخاطبان</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    @forelse($clubCards['topUsers'] as $index => $leader)
                        @php
                            $leaderUser = $leader->user;
                            $initial = mb_substr(trim($leaderUser?->first_name ?: $leaderUser?->last_name ?: '؟'), 0, 1);
                        @endphp
                        <div class="flex items-center gap-3 rounded-xl px-2 py-1.5 transition group-hover:bg-white/40 dark:group-hover:bg-slate-900/40">
                            <span class="mw-top-rank mw-top-rank-{{ $index + 1 }}">{{ $index + 1 }}</span>

                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-slate-500 to-slate-700 text-xs font-black text-white dark:from-slate-600 dark:to-slate-800">
                                {{ $initial }}
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ trim(($leaderUser?->first_name ?? '') . ' ' . ($leaderUser?->last_name ?? '')) ?: 'کاربر مودینگ‌وی' }}
                                </span>
                            </span>

                            <span class="shrink-0 text-left">
                                <b class="block text-xs font-black text-brand-600 dark:text-brand-400">{{ number_format($leader->balance) }}</b>
                                <span class="block text-[9.5px] font-semibold text-slate-400">امتیاز</span>
                            </span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 px-4 py-6 text-center text-xs text-slate-500 dark:text-slate-400">
                            هنوز امتیازی ثبت نشده است.
                        </div>
                    @endforelse
                </div>

                <span class="mt-auto pt-3 inline-flex items-center justify-center gap-2 border-t border-slate-200 pt-3 text-xs font-extrabold text-brand-700 transition group-hover:text-brand-800 dark:border-slate-700/70 dark:text-brand-300">
                    مشاهده صفحه کاربران برتر
                    <span class="[&_svg]:h-4 [&_svg]:w-4">
                        @include('icons.arrow-left-02')
                    </span>
                </span>
            </div>
        </a>
    </div>
</section>
