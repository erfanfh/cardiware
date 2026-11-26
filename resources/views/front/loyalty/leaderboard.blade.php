@extends('front.master')

@section('head')
    <title>Community Club leaders | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:py-8">
    @php
        $leaders = $leaderboard['leaders'];
        $champion = $leaders->first();
        $runnersUp = $leaders->slice(1, 2);
        $remainingLeaders = $leaders->slice(3);
        $currentMember = $leaderboard['currentMember'];
        $stats = $leaderboard['stats'];
        $period = $leaderboard['period'];
        $levelResolver = app(\App\Application\Loyalty\Actions\ResolveUserLevelAction::class);
        $levelFor = static fn ($account) => $account && (int) $account->lifetime_xp > 0
            ? $levelResolver->execute((int) $account->lifetime_xp)
            : null;
        $nameFor = static function ($member): string {
            foreach ([$member?->display_name, $member?->full_name, $member?->username] as $candidate) {
                $candidate = trim((string) $candidate);

                if ($candidate !== '') {
                    return $candidate;
                }
            }

            return 'Club member';
        };
    @endphp

    <div class="space-y-5 sm:space-y-6">
        <section class="relative isolate overflow-hidden rounded-3xl border border-amber-200/80 bg-white shadow-sm dark:border-amber-500/20 dark:bg-slate-900">
            <div class="absolute inset-0 -z-20 bg-gradient-to-br from-amber-50 via-white to-brand-50 dark:from-amber-500/10 dark:via-slate-900 dark:to-brand-950/30"></div>
            <div class="absolute -right-20 -top-24 -z-10 h-64 w-64 rounded-full bg-amber-300/30 blur-3xl dark:bg-amber-500/10"></div>
            <div class="absolute -bottom-28 left-8 -z-10 h-64 w-64 rounded-full bg-brand-300/25 blur-3xl dark:bg-brand-500/10"></div>
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-amber-300 via-brand-500 to-rose-500"></div>

            <div class="grid gap-6 p-5 sm:p-7 xl:grid-cols-12 xl:items-center">
                <div class="xl:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white/75 px-3 py-1.5 text-[11px] font-extrabold text-amber-700 shadow-sm backdrop-blur dark:border-amber-500/20 dark:bg-slate-900/70 dark:text-amber-300">
                        <span class="text-amber-500 [&_svg]:h-4 [&_svg]:w-4">@include('icons.crown-03')</span>
                        Hall of Fame Cardiware
                    </div>

                    <h1 class="mt-4 text-2xl font-black leading-10 text-slate-950 sm:text-3xl dark:text-white">
                        Community Club leaders
                    </h1>
                    <p class="mt-2 max-w-2xl text-xs font-medium leading-7 text-slate-600 sm:text-sm dark:text-slate-300">
                        Competition {{ $period->label }}: Rank is based on XP earned during this period; spending points on rewards does not lower your rank.
                        Purchases, premium subscriptions and affiliate sales earn the most XP are provided.
                    </p>

                    <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <a href="{{ route('home') }}"
                           class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 text-xs font-extrabold text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-brand-600 dark:bg-white dark:text-slate-900 dark:hover:bg-brand-300">
                            Back to home
                            <span class="rotate-180 [&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                        </a>
                        <a href="{{ route('loyalty.hall-of-fame') }}"
                           class="inline-flex h-11 items-center justify-center rounded-2xl bg-amber-400 px-4 text-xs font-extrabold text-slate-950 transition hover:bg-amber-300">
                            👑 Hall of Fame
                        </a>
                        <a href="{{ auth()->check() ? route('account.loyalty') : route('login') }}"
                           class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white/70 px-4 text-xs font-extrabold text-slate-700 backdrop-blur transition hover:border-brand-200 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:text-brand-300">
                            {{ auth()->check() ? 'My club' : 'Sign in' }}
                        </a>
                    </div>
                </div>

                <div class="xl:col-span-5">
                    <div class="relative overflow-hidden rounded-3xl border border-white/80 bg-white/80 p-4 shadow-xl shadow-amber-900/5 backdrop-blur sm:p-5 dark:border-slate-700 dark:bg-slate-950/65 dark:shadow-black/20">
                        <div class="absolute -left-8 -top-8 h-24 w-24 rounded-full bg-brand-200/40 blur-2xl dark:bg-brand-500/10"></div>
                        @auth
                            <div class="relative flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-[10px] font-extrabold text-slate-400">Your current rank</div>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        <span class="text-3xl font-black text-slate-950 dark:text-white">
                                            {{ $currentMember['rank'] ? number_format($currentMember['rank']) : '—' }}
                                        </span>
                                        @if($currentMember['rank'])
                                            <span class="text-[11px] font-bold text-slate-400">of {{ number_format($stats['members']) }} people</span>
                                        @else
                                            <span class="text-[11px] font-bold text-slate-400">You are not ranked yet</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-brand-500 text-white shadow-lg shadow-brand-500/20 [&_svg]:h-6 [&_svg]:w-6">
                                    @include('icons.medal-06')
                                </span>
                            </div>

                            <div class="relative mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-900/80">
                                    <div class="text-[9px] font-extrabold text-slate-400">XP in {{ $period->key === 'all' ? 'All time' : 'This period' }}</div>
                                    <div class="mt-1 text-base font-black text-brand-600 dark:text-brand-300">{{ number_format($currentMember['score']) }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-900/80">
                                    <div class="text-[9px] font-extrabold text-slate-400">Spendable points</div>
                                    <div class="mt-1 text-base font-black text-slate-800 dark:text-slate-100">{{ number_format($currentMember['balance']) }}</div>
                                </div>
                            </div>

                            @if($currentMember['is_staff'])
                                <p class="relative mt-3 rounded-xl bg-slate-100 px-3 py-2 text-[10px] font-bold leading-5 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    Staff accounts are excluded from the leaderboard to keep competition fair.
                                </p>
                            @elseif($currentMember['rank'] && !$currentMember['is_visible'])
                                <p class="relative mt-3 rounded-xl bg-brand-50 px-3 py-2 text-[10px] font-bold leading-5 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                    Your rank is outside the top 50; earn more points and check again.
                                </p>
                            @elseif(!$currentMember['rank'])
                                <p class="relative mt-3 rounded-xl bg-amber-50 px-3 py-2 text-[10px] font-bold leading-5 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                    This period you have not yet XP earned any; earn your first points to join the leaderboard.
                                </p>
                            @endif
                        @else
                            <div class="relative flex items-start gap-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-amber-400 to-brand-500 text-white shadow-lg shadow-brand-500/20 [&_svg]:h-6 [&_svg]:w-6">
                                    @include('icons.user-star-01')
                                </span>
                                <div>
                                    <div class="text-sm font-black text-slate-900 dark:text-white">Find your rank</div>
                                    <p class="mt-1 text-[11px] font-medium leading-6 text-slate-500 dark:text-slate-400">Sign in to see your rank, current points and path to the top.</p>
                                </div>
                            </div>
                            <a href="{{ route('login') }}" class="relative mt-4 inline-flex h-10 w-full items-center justify-center rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-400">Sign in and view my rank</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <nav class="flex flex-wrap items-center gap-2" aria-label="Competition period">
            @foreach($leaderboard['periods'] as $periodKey => $periodLabel)
                <a href="{{ route('loyalty.leaderboard', $periodKey === 'month' ? [] : ['period' => $periodKey]) }}"
                   @class([
                       'inline-flex h-10 items-center rounded-2xl border px-4 text-xs font-extrabold transition',
                       'border-brand-500 bg-brand-500 text-white shadow-lg shadow-brand-500/20' => $period->key === $periodKey,
                       'border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' => $period->key !== $periodKey,
                   ])>
                    {{ $periodLabel }}
                </a>
            @endforeach
            <span class="ms-auto text-[11px] font-bold text-slate-400">{{ $period->label }}</span>
        </nav>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach([
                ['label' => 'Ranked members', 'value' => $stats['members'], 'icon' => 'icons.user-multiple-02', 'tone' => 'brand'],
                ['label' => 'Total XP Period', 'value' => $stats['total_points'], 'icon' => 'icons.star', 'tone' => 'amber'],
                ['label' => 'Average XP', 'value' => $stats['average_points'], 'icon' => 'icons.chart-03', 'tone' => 'sky'],
                ['label' => 'Highest XP', 'value' => $stats['highest_points'], 'icon' => 'icons.crown-02', 'tone' => 'rose'],
            ] as $stat)
                @php
                    $toneClasses = match($stat['tone']) {
                        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
                        'sky' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
                        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
                        default => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300',
                    };
                @endphp
                <article class="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl sm:h-11 sm:w-11 {{ $toneClasses }} [&_svg]:h-5 [&_svg]:w-5">
                        @include($stat['icon'])
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-[9px] font-extrabold text-slate-400 sm:text-[10px]">{{ $stat['label'] }}</div>
                        <div class="mt-1 truncate text-lg font-black text-slate-900 sm:text-xl dark:text-white">{{ number_format($stat['value']) }}</div>
                    </div>
                </article>
            @endforeach
        </section>

        @if($leaders->isNotEmpty())
            <section>
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-[10px] font-extrabold text-amber-600 dark:text-amber-300">Champions podium</div>
                        <h2 class="mt-1 text-lg font-black text-slate-950 dark:text-white">Top three club members</h2>
                    </div>
                    <span class="hidden rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-500 sm:inline-flex dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                        Automatically updates when points change
                    </span>
                </div>

                <div class="grid gap-3 xl:grid-cols-12">
                    @php
                        $championName = $nameFor($champion->user);
                        $championLevel = $levelFor($champion);
                    @endphp
                    <article class="relative isolate overflow-hidden rounded-3xl border border-amber-300 bg-gradient-to-br from-amber-400 via-brand-500 to-orange-600 p-5 text-white shadow-xl shadow-brand-500/15 sm:p-6 xl:col-span-5">
                        <div class="absolute -left-12 -top-12 -z-10 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
                        <div class="absolute -bottom-16 -right-8 -z-10 h-44 w-44 rounded-full bg-rose-800/25 blur-3xl"></div>
                        <div class="absolute left-5 top-5 text-white/15 [&_svg]:h-24 [&_svg]:w-24">@include('icons.crown-03')</div>

                        <div class="relative flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="relative shrink-0">
                                    <img src="{{ $champion->user?->profileAvatarUrl() ?: asset('img/user-avatar.png') }}"
                                         alt="{{ $championName }}"
                                         class="h-16 w-16 rounded-3xl border-2 border-white/70 object-cover shadow-xl sm:h-20 sm:w-20">
                                    <span class="absolute -bottom-2 -left-2 grid h-8 min-w-8 place-items-center rounded-xl border-2 border-amber-300 bg-white px-1 text-sm font-black text-amber-600 shadow-lg">1</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-extrabold text-white/70">Club champion</div>
                                    <h3 class="mt-1 flex items-center gap-2 truncate text-lg font-black sm:text-xl"><a href="{{ route('loyalty.members.show', $champion->user_id) }}" class="truncate hover:underline">{{ $championName }}</a>@if($champion->user?->has_active_vip_subscription)<x-front.discussion.comment-vip-badge :user-id="$champion->user_id" />@endif</h3>
                                    <div class="mt-1 text-[10px] font-bold text-white/70">
                                        First place {{ $period->label }}
                                        @if($championLevel && (int) $championLevel->min_xp > 0)
                                            · {{ $championLevel->badge }} {{ $championLevel->title }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="shrink-0 text-amber-100 [&_svg]:h-7 [&_svg]:w-7">@include('icons.crown-02')</span>
                        </div>

                        <div class="relative mt-7 flex items-end justify-between gap-4 rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur">
                            <div>
                                <div class="text-[10px] font-bold text-white/65">XP Period</div>
                                <div class="mt-1 text-3xl font-black tracking-tight">{{ number_format($champion->leaderboard_score) }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-[10px] font-bold text-white/65">XP Total</div>
                                <div class="mt-1 text-sm font-black">{{ number_format($champion->lifetime_xp) }}</div>
                            </div>
                        </div>
                    </article>

                    <div class="grid gap-3 sm:grid-cols-2 xl:col-span-7">
                        @foreach($runnersUp as $account)
                            @php
                                $rank = (int) $account->leaderboard_rank;
                                $memberName = $nameFor($account->user);
                                $isSecond = $rank === 2;
                                $memberLevel = $levelFor($account);
                            @endphp
                            <article class="relative overflow-hidden rounded-3xl border p-5 shadow-sm {{ $isSecond ? 'border-slate-300 bg-gradient-to-br from-slate-100 via-white to-slate-50 dark:border-slate-600 dark:from-slate-700/70 dark:via-slate-900 dark:to-slate-900' : 'border-orange-200 bg-gradient-to-br from-orange-50 via-white to-amber-50/40 dark:border-orange-500/20 dark:from-orange-500/10 dark:via-slate-900 dark:to-slate-900' }}">
                                <div class="absolute -left-8 -top-8 h-24 w-24 rounded-full {{ $isSecond ? 'bg-slate-300/35 dark:bg-slate-500/10' : 'bg-orange-200/50 dark:bg-orange-500/10' }} blur-2xl"></div>
                                <div class="relative flex h-full flex-col">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <img src="{{ $account->user?->profileAvatarUrl() ?: asset('img/user-avatar.png') }}"
                                                 alt="{{ $memberName }}"
                                                 class="h-14 w-14 shrink-0 rounded-2xl border-2 object-cover shadow-sm {{ $isSecond ? 'border-slate-300 dark:border-slate-600' : 'border-orange-200 dark:border-orange-500/30' }}">
                                            <div class="min-w-0">
                                                <div class="text-[9px] font-extrabold {{ $isSecond ? 'text-slate-500 dark:text-slate-400' : 'text-orange-600 dark:text-orange-300' }}">Rank {{ number_format($rank) }}</div>
                                                <h3 class="mt-1 flex items-center gap-1.5 truncate text-sm font-black text-slate-900 dark:text-white"><a href="{{ route('loyalty.members.show', $account->user_id) }}" class="truncate hover:text-brand-600">{{ $memberName }}</a>@if($account->user?->has_active_vip_subscription)<x-front.discussion.comment-vip-badge :user-id="$account->user_id" />@endif</h3>
                                                @if($memberLevel && (int) $memberLevel->min_xp > 0)
                                                    <span class="mt-1 inline-flex rounded-lg border px-1.5 py-0.5 text-[9px] font-black {{ $memberLevel->colorClasses()['chip'] }}">{{ $memberLevel->badge }} {{ $memberLevel->title }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="grid h-9 min-w-9 shrink-0 place-items-center rounded-xl px-1 text-sm font-black {{ $isSecond ? 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-200' : 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300' }}">
                                            {{ number_format($rank) }}
                                        </span>
                                    </div>

                                    <div class="mt-5 grid grid-cols-2 gap-2 border-t border-slate-200/70 pt-4 dark:border-slate-700/70">
                                        <div>
                                            <div class="text-[9px] font-bold text-slate-400">XP Period</div>
                                            <div class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ number_format($account->leaderboard_score) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[9px] font-bold text-slate-400">XP Total</div>
                                            <div class="mt-1 text-sm font-black text-slate-600 dark:text-slate-300">{{ number_format($account->lifetime_xp) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        @if($runnersUp->count() < 2)
                            <div class="grid min-h-44 place-items-center rounded-3xl border border-dashed border-slate-300 bg-slate-50/70 p-5 text-center dark:border-slate-700 dark:bg-slate-800/40">
                                <div>
                                    <span class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-white text-slate-300 shadow-sm dark:bg-slate-900 dark:text-slate-600 [&_svg]:h-5 [&_svg]:w-5">@include('icons.user-star-01')</span>
                                    <p class="mt-3 text-xs font-extrabold text-slate-500 dark:text-slate-400">This spot awaits a new contender</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            @if($remainingLeaders->isNotEmpty())
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5 dark:border-slate-800">
                        <div>
                            <div class="text-[10px] font-extrabold text-brand-500 dark:text-brand-300">Continue competing</div>
                            <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white">Top members leaderboard</h2>
                        </div>
                        <span class="rounded-xl bg-slate-100 px-2.5 py-1.5 text-[10px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">Top 50</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($remainingLeaders as $account)
                            @php
                                $rank = (int) $account->leaderboard_rank;
                                $memberName = $nameFor($account->user);
                                $isCurrentUser = $user && $account->user_id === $user->getKey();
                                $memberLevel = $levelFor($account);
                            @endphp
                            <article class="group flex items-center gap-3 px-4 py-3.5 transition sm:gap-4 sm:px-5 {{ $isCurrentUser ? 'bg-brand-50/80 dark:bg-brand-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                                <span class="grid h-9 min-w-9 shrink-0 place-items-center rounded-xl px-1 text-xs font-black {{ $rank <= 10 ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ number_format($rank) }}
                                </span>

                                <img src="{{ $account->user?->profileAvatarUrl() ?: asset('img/user-avatar.png') }}"
                                     alt="{{ $memberName }}"
                                     class="h-11 w-11 shrink-0 rounded-2xl border object-cover shadow-sm {{ $account->user?->has_active_vip_subscription ? 'border-amber-400 ring-2 ring-amber-400/60' : 'border-slate-200 dark:border-slate-700' }}">

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="truncate text-xs font-black text-slate-800 sm:text-sm dark:text-slate-100"><a href="{{ route('loyalty.members.show', $account->user_id) }}" class="hover:text-brand-600">{{ $memberName }}</a></h3>
                                        @if($account->user?->has_active_vip_subscription)
                                            <x-front.discussion.comment-vip-badge :user-id="$account->user_id" />
                                        @endif
                                        @if($isCurrentUser)
                                            <span class="shrink-0 rounded-lg bg-brand-500 px-2 py-0.5 text-[8px] font-black text-white">You</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 hidden text-[10px] font-bold text-slate-400 sm:block">
                                        @if($memberLevel && (int) $memberLevel->min_xp > 0)
                                            {{ $memberLevel->badge }} {{ $memberLevel->title }} ·
                                        @endif
                                        {{ number_format($account->lifetime_xp) }} XP Total
                                    </div>
                                </div>

                                <div class="shrink-0 text-end">
                                    <div class="text-sm font-black text-slate-900 sm:text-base dark:text-white">{{ number_format($account->leaderboard_score) }}</div>
                                    <div class="mt-0.5 text-[9px] font-bold text-slate-400">XP Period</div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @else
            <section class="relative overflow-hidden rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center sm:p-12 dark:border-slate-700 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-amber-50/80 to-transparent dark:from-amber-500/5"></div>
                <div class="relative">
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-amber-50 text-amber-500 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20 [&_svg]:h-8 [&_svg]:w-8">@include('icons.crown-03')</span>
                    <h2 class="mt-5 text-base font-black text-slate-900 sm:text-lg dark:text-white">No one is on the club podium yet</h2>
                    <p class="mx-auto mt-2 max-w-lg text-xs leading-7 text-slate-500 sm:text-sm dark:text-slate-400">The first positive points will add the first name to this Hall of Fame. You could be the first champion.</p>
                    <a href="{{ auth()->check() ? route('account.loyalty') . '#ways-to-earn' : route('login') }}" class="mt-5 inline-flex h-11 items-center justify-center rounded-2xl bg-brand-500 px-5 text-xs font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-400">{{ auth()->check() ? 'Start earning points' : 'Sign in' }}</a>
                </div>
            </section>
        @endif

        <p class="px-2 text-center text-[10px] font-medium leading-6 text-slate-400 dark:text-slate-500">
            Ranking based on XP earned during the period (refunded order points are deducted); for equal XP scores, XP lifetime total and then account age determine rank. Staff do not compete.
        </p>
    </div>
    </section>
@endsection
