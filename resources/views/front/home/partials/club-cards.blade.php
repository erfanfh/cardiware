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

                {{-- تصویر اختیاری کارت --}}
                @if(!empty($clubCards['vip']['image_path']))
                    <div class="relative mt-3 overflow-hidden rounded-2xl ring-1 ring-amber-500/20">
                        <img src="{{ $clubCards['vip']['image_path'] }}" alt="{{ $clubCards['vip']['title'] }}"
                             class="h-36 w-full object-cover transition duration-500 group-hover:scale-[1.04] sm:h-40"
                             loading="lazy">
                        <span class="absolute inset-0 bg-gradient-to-t from-amber-950/45 via-transparent to-transparent" aria-hidden="true"></span>
                    </div>
                @endif

                {{-- توضیح کامل؛ وسط کارت خالی نمی‌ماند --}}
                @if(!empty($clubCards['vip']['description']))
                    <div class="mt-3 rounded-2xl border border-amber-500/15 bg-amber-50/60 px-3.5 py-3 text-[11.5px] leading-6 whitespace-pre-line text-slate-700 dark:border-amber-500/15 dark:bg-amber-500/5 dark:text-slate-300">
                        {{ $clubCards['vip']['description'] }}
                    </div>
                @endif

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

            <div class="mw-club-scroll max-h-[26rem] space-y-2.5 overflow-y-auto pl-1 [-ms-overflow-style:none] [scrollbar-width:thin]">
                @forelse($clubCards['projects'] as $project)
                    @php
                        $clubProgress = max(0, min(100, (int)($project->progress_percent ?? 0)));
                        $projectDone = $clubProgress >= 100;
                        $clubHue = (int) round($clubProgress * 1.42);
                    @endphp
                    <a href="{{ front_route('projects.detail', $project) }}"
                       class="group relative block overflow-hidden rounded-2xl border border-transparent bg-slate-50/80 px-4 py-3 ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:ring-brand-300/60 dark:bg-slate-900/70 dark:ring-slate-800 dark:hover:bg-slate-900 dark:hover:ring-brand-700/50">
                        <span class="absolute inset-y-0 right-0 w-1 rounded-full {{ $projectDone ? 'bg-emerald-500' : 'bg-brand-500' }}" aria-hidden="true"></span>

                        <span class="flex items-center justify-between gap-2">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="mw-club-projects-dot {{ $projectDone ? 'is-done' : '' }}" aria-hidden="true"></span>

                                <span class="truncate text-sm font-extrabold text-slate-800 transition group-hover:text-brand-700 dark:text-slate-200 dark:group-hover:text-brand-300">
                                    {{ $project->title }}
                                </span>
                            </span>

                            <span class="shrink-0 text-[11px] font-black {{ $projectDone ? 'text-emerald-600 dark:text-emerald-400' : 'text-brand-600 dark:text-brand-400' }}">
                                {{ $clubProgress }}٪
                            </span>
                        </span>

                        @if($project->excerpt())
                            <span class="mt-1.5 block text-[11px] leading-5 text-slate-500 line-clamp-2 dark:text-slate-400">
                                {{ $project->excerpt() }}
                            </span>
                        @endif

                        {{-- نوار پیشرفت مینی‌مال هم‌رنگ درصد --}}
                        <span class="mt-2 block h-1.5 overflow-hidden rounded-full bg-slate-200/80 dark:bg-slate-800" aria-hidden="true">
                            <span class="block h-full rounded-full transition-all duration-700"
                                  style="width: {{ $clubProgress }}%; background: linear-gradient(to left, hsl({{ min(160, $clubHue + 16) }} 82% 46%), hsl({{ $projectDone ? 152 : $clubHue }} 84% 52%));"></span>
                        </span>
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

                <div class="mw-club-scroll max-h-[26rem] space-y-1.5 overflow-y-auto pl-1 [-ms-overflow-style:none] [scrollbar-width:thin]">
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
