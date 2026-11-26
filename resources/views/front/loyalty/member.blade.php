@extends('front.master')

@php
    // Display name or username; real name (full_name) is not shown on the public page.
    $memberName = collect([$member->display_name, $member->username])
        ->map(fn ($value) => trim((string) $value))
        ->first(fn ($value) => $value !== '') ?? 'Club member';
    $colors = $level->colorClasses();
    $vipTheme = \App\Application\Loyalty\Support\VipClubProfile::theme($member->club_theme);
    $trophiesCount = $trophies->count();
    $achievementsCount = $achievements->count();
@endphp

@section('head')
    <title>{{ $memberName }} in the Community Club | {{ get_setting('general', 'title') }}</title>
    <meta name="robots" content="noindex">
@endsection

@section('content')
    <section class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:py-8">

        {{-- ═══════ Profile card: themed cover + Glowing avatar + Stats bar ═══════ --}}
        <section class="relative isolate overflow-hidden rounded-[2rem] border {{ $isVip ? 'border-amber-200 shadow-[0_18px_50px_-18px_rgba(245,158,11,.35)] dark:border-amber-500/25 dark:shadow-[0_18px_50px_-18px_rgba(245,158,11,.15)]' : 'border-slate-200 shadow-sm dark:border-slate-800' }} bg-white dark:bg-slate-900">

            {{-- Cover: member theme gradient + Light texture and subtle shine --}}
            <div class="relative h-40 overflow-hidden sm:h-44 {{ $isVip ? 'bg-gradient-to-l ' . $vipTheme['header'] : 'bg-gradient-to-l ' . $colors['bar'] . ' opacity-90' }}">
                @if(! $isVip)
                    <div class="absolute inset-0 bg-white/70 dark:bg-slate-900/70"></div>
                @endif

                {{-- Cover light texture --}}
                <div class="absolute inset-0 opacity-60"
                     style="background-image: radial-gradient(circle at 18% 120%, rgba(255,255,255,.28), transparent 42%), radial-gradient(circle at 82% -30%, rgba(255,255,255,.22), transparent 38%);"></div>
                <div class="absolute inset-0 opacity-[.07]"
                     style="background-image: repeating-linear-gradient(115deg, #fff 0 2px, transparent 2px 26px);"></div>

                @if($isVip)
                    {{-- Subtle cover shine VIP --}}
                    <div class="mw-member-shine pointer-events-none absolute inset-y-0 -left-1/3 w-1/3 bg-gradient-to-l from-transparent via-white/25 to-transparent"></div>
                @endif

                {{-- Badge VIP --}}
                @if($isVip)
                    <div class="absolute right-4 top-4 inline-flex items-center gap-1.5 rounded-2xl border border-white/30 bg-slate-950/35 px-3.5 py-1.5 text-xs font-black text-white shadow-lg backdrop-blur-md">
                        <span class="text-sm">👑</span> Member VIP
                    </div>
                @endif

                {{-- Follow: each user except the member can follow once --}}
                @php
                    $isSelf = auth()->check() && auth()->id() === $member->getKey();
                @endphp
                @if($isSelf)
                    <div class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-2xl border border-white/30 bg-slate-950/35 px-3.5 py-1.5 text-xs font-black text-white shadow-lg backdrop-blur-md"
                         title="Your follower count">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#fb7185" aria-hidden="true">
                            <path d="M12 21s-7.5-4.7-10-9.3C.4 8.6 2.4 5 6 5c2.2 0 3.6 1.2 4.4 2.5h3.2C14.4 6.2 15.8 5 18 5c3.6 0 5.6 3.6 4 6.7C19.5 16.3 12 21 12 21Z"/>
                        </svg>
                        {{ number_format($followersCount) }}
                        <span class="font-bold text-white/70">Followers</span>
                    </div>
                @elseif(auth()->check())
                    <form method="POST" action="{{ route('loyalty.members.follow', $member) }}"
                          class="absolute left-4 top-4"
                          x-data="{ busy: false }" x-on:submit="busy = true">
                        @csrf
                        <button type="submit" x-bind:disabled="busy"
                                class="group inline-flex cursor-pointer items-center gap-1.5 rounded-2xl border px-3.5 py-1.5 text-xs font-black shadow-lg backdrop-blur-md transition
                                       {{ $isFollowing
                                           ? 'border-rose-300/60 bg-rose-500/85 text-white hover:bg-rose-600'
                                           : 'border-white/30 bg-slate-950/35 text-white hover:bg-slate-950/55' }}"
                                title="{{ $isFollowing ? 'Unfollow' : 'Follow this member' }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $isFollowing ? '#ffffff' : 'none' }}" stroke="{{ $isFollowing ? 'none' : '#fb7185' }}" stroke-width="1.5" aria-hidden="true">
                                <path d="M12 21s-7.5-4.7-10-9.3C.4 8.6 2.4 5 6 5c2.2 0 3.6 1.2 4.4 2.5h3.2C14.4 6.2 15.8 5 18 5c3.6 0 5.6 3.6 4 6.7C19.5 16.3 12 21 12 21Z"/>
                            </svg>
                            <span class="tabular-nums">{{ number_format($followersCount) }}</span>
                            <span class="font-bold {{ $isFollowing ? 'text-white/85' : 'text-white/70' }}">{{ $isFollowing ? 'Following' : 'Followers' }}</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-2xl border border-white/30 bg-slate-950/35 px-3.5 py-1.5 text-xs font-black text-white shadow-lg backdrop-blur-md transition hover:bg-slate-950/55"
                       title="Sign in to follow">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#fb7185" aria-hidden="true">
                            <path d="M12 21s-7.5-4.7-10-9.3C.4 8.6 2.4 5 6 5c2.2 0 3.6 1.2 4.4 2.5h3.2C14.4 6.2 15.8 5 18 5c3.6 0 5.6 3.6 4 6.7C19.5 16.3 12 21 12 21Z"/>
                        </svg>
                        {{ number_format($followersCount) }}
                        <span class="font-bold text-white/70">Followers</span>
                    </a>
                @endif
            </div>

            {{-- Body: avatar + Details --}}
            <div class="relative px-5 pb-5 sm:px-7">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-end sm:gap-6">
                    {{-- Avatar with two-layer theme-colored halo --}}
                    <div class="relative -mt-14 shrink-0 sm:-mt-16">
                        <div class="rounded-[1.75rem] {{ $isVip ? 'p-[3px] bg-gradient-to-br ' . $vipTheme['header'] : 'p-[3px] bg-gradient-to-br ' . $colors['bar'] }}">
                            <img src="{{ $member->profileAvatarUrl() ?: asset('img/user-avatar.png') }}" alt="{{ $memberName }}"
                                 class="h-28 w-28 rounded-[1.6rem] bg-white object-cover dark:bg-slate-900 sm:h-32 sm:w-32">
                        </div>
                        <span class="absolute -bottom-1.5 -left-1.5 grid h-8 w-8 place-items-center rounded-2xl border-2 border-white bg-gradient-to-br {{ $colors['bar'] }} text-[11px] font-black text-white shadow-md dark:border-slate-900">
                            {{ $level->badge }}
                        </span>
                    </div>

                    <div class="min-w-0 flex-1 pb-1 text-center sm:text-start">
                        <h1 class="text-2xl font-black leading-snug text-slate-950 dark:text-white sm:text-[1.7rem]">
                            {{ $memberName }}
                            @if($isVip)
                                <span class="mr-1 align-middle text-lg" title="Member VIP">👑</span>
                            @endif
                        </h1>

                        <div class="mt-2.5 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            <span class="inline-flex items-center gap-1.5 rounded-2xl border px-3 py-1.5 text-[11px] font-black {{ $colors['chip'] }}">
                                {{ $level->badge }} {{ $level->title }}
                            </span>
                            @if($isVip)
                                <span class="inline-flex items-center gap-1 rounded-2xl border px-3 py-1.5 text-[11px] font-black {{ $vipTheme['chip'] }}">
                                    VIP{{ $vipSince ? ' of ' . (($vipSince) ? \Illuminate\Support\Carbon::parse($vipSince)->format('F Y') : '') : '' }}
                                </span>
                            @endif
                            @if($isElite)
                                <span class="inline-flex items-center gap-1 rounded-2xl border border-violet-300 bg-violet-50 px-3 py-1.5 text-[11px] font-black text-violet-700 dark:border-violet-500/40 dark:bg-violet-500/10 dark:text-violet-300">
                                    💎 Elite Circle
                                </span>
                            @endif
                            @if($monthRank)
                                <span class="inline-flex items-center gap-1 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-black text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    🏅 Rank {{ number_format($monthRank) }} This month
                                </span>
                            @endif
                        </div>

                        @if($isVip && filled($member->club_bio) && $member->club_bio_status === 'approved')
                            <p class="mx-auto mt-3 max-w-xl rounded-2xl border border-dashed {{ $vipTheme['chip'] }} px-4 py-2.5 text-sm leading-7 text-slate-600 dark:text-slate-300 sm:mx-0">
                                «{{ $member->club_bio }}»
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Progress bar XP --}}
                <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="flex items-end justify-between gap-3">
                        <div class="text-center sm:text-start">
                            <div class="text-xl font-black tabular-nums text-slate-900 dark:text-white">{{ number_format($xp) }} <span class="text-xs font-bold text-slate-400">XP</span></div>
                            <div class="mt-0.5 text-[10.5px] font-bold text-slate-400">Club points</div>
                        </div>
                        <div class="h-9 w-px bg-slate-200 dark:bg-slate-700"></div>
                        <div class="text-center sm:text-start">
                            @if($nextLevel)
                                <div class="text-sm font-black text-slate-700 dark:text-slate-200">{{ number_format(max(0, $nextLevel->min_xp - $xp)) }} XP</div>
                                <div class="mt-0.5 text-[10.5px] font-bold text-slate-400">to level {{ $nextLevel->title }}</div>
                            @else
                                <div class="text-sm font-black {{ $colors['text'] ?? 'text-amber-600 dark:text-amber-300' }}">Completed level</div>
                                <div class="mt-0.5 text-[10.5px] font-bold text-slate-400">Highest club level</div>
                            @endif
                        </div>
                        {{-- Progress percentage --}}
                        <div class="ms-auto hidden sm:block">
                            <div class="grid h-14 w-14 place-items-center rounded-2xl border {{ $colors['chip'] }} text-sm font-black tabular-nums">
                                {{ $progress }}%
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200/70 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-l {{ $colors['bar'] }} shadow-[0_0_10px_rgba(249,115,22,.35)] transition-[width] duration-700"
                             style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                {{-- Stats bar: trophies, badges, level --}}
                <div class="mt-4 grid grid-cols-3 divide-x divide-slate-100 divide-x-reverse overflow-hidden rounded-2xl border border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex flex-col items-center gap-1 bg-white px-3 py-3.5 dark:bg-slate-900">
                        <span class="text-lg">🏆</span>
                        <span class="text-base font-black tabular-nums text-slate-900 dark:text-white">{{ number_format($trophiesCount) }}</span>
                        <span class="text-[10.5px] font-bold text-slate-400">Trophies and honors</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 bg-white px-3 py-3.5 dark:bg-slate-900">
                        <span class="text-lg">🎖️</span>
                        <span class="text-base font-black tabular-nums text-slate-900 dark:text-white">{{ number_format($achievementsCount) }}</span>
                        <span class="text-[10.5px] font-bold text-slate-400">Honor badge</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 bg-white px-3 py-3.5 dark:bg-slate-900">
                        <span class="text-lg">🚀</span>
                        <span class="text-base font-black text-slate-900 dark:text-white">{{ $level->badge }} {{ $level->title }}</span>
                        <span class="text-[10.5px] font-bold text-slate-400">Club level</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══════ Points earned by activity) ═══════ --}}
        @if($earnedByReason->isNotEmpty())
            <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-brand-50 text-base ring-1 ring-brand-200/70 dark:bg-brand-500/10 dark:ring-brand-500/20">⭐</span>
                        Points earned
                    </h2>
                </div>
                <div class="mt-4 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($earnedByReason as $row)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/60">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-sm font-black text-brand-600 dark:text-brand-400">
                                +{{ number_format($row['total']) }}
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-xs font-black text-slate-800 dark:text-slate-200">{{ $row['label'] }}</div>
                                <div class="mt-0.5 text-[10px] font-bold text-slate-400">{{ number_format($row['count']) }} times</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ═══════ Trophies and honors ═══════ --}}
        <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                    <span class="grid h-9 w-9 place-items-center rounded-2xl bg-amber-50 text-base ring-1 ring-amber-200/70 dark:bg-amber-500/10 dark:ring-amber-500/20">🏆</span>
                    Trophies and honors
                </h2>
                <span class="rounded-xl bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ number_format($trophiesCount) }} Trophy</span>
            </div>
            @if($trophies->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-xs leading-7 text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    No trophies yet; monthly, seasonal and annual leaders earn trophies here.
                </div>
            @else
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($trophies as $trophy)
                        <div class="group flex items-center gap-3 rounded-2xl border border-amber-200/70 bg-gradient-to-l from-amber-50/80 to-white p-3.5 transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-500/20 dark:from-amber-500/5 dark:to-slate-900">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-amber-200/60 dark:bg-slate-900 dark:ring-amber-500/20">{{ $trophy->trophy() }}</span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $trophy->title() }}</div>
                                <div class="mt-0.5 text-[10px] font-bold text-slate-400">{{ number_format($trophy->score) }} XP During that period</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ═══════ Badges ═══════ --}}
        <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                    <span class="grid h-9 w-9 place-items-center rounded-2xl bg-violet-50 text-base ring-1 ring-violet-200/70 dark:bg-violet-500/10 dark:ring-violet-500/20">🎖️</span>
                    Badges
                </h2>
                <span class="rounded-xl bg-violet-50 px-2.5 py-1 text-xs font-black text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">{{ number_format($achievementsCount) }} Badge</span>
            </div>
            @if($achievements->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    No badges earned yet.
                </div>
            @else
                <div class="mt-4 flex flex-wrap gap-2.5">
                    @foreach($achievements as $achievement)
                        <span class="group inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 px-3.5 py-2.5 text-xs font-black text-slate-700 transition hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-50/60 hover:shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-violet-500/40 dark:hover:bg-violet-500/10">
                            <span class="text-lg">{{ $achievement->badge }}</span> {{ $achievement->title }}
                        </span>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="flex flex-wrap justify-center gap-2.5 pt-1">
            <a href="{{ route('loyalty.hall-of-fame') }}"
               class="inline-flex h-11 items-center gap-2 rounded-2xl bg-slate-950 px-6 text-xs font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl dark:bg-white dark:text-slate-900">
                🏛️ Hall of Fame
            </a>
            <a href="{{ route('loyalty.leaderboard') }}"
               class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-xs font-black text-slate-700 transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                📊 Leaderboard
            </a>
        </div>
    </section>
@endsection
