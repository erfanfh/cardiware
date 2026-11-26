@extends('front.master')

@section('head')
    <title>تالار افتخار باشگاه | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    @php
        $nameFor = static function ($member): string {
            // نام نمایشی یا نام کاربری؛ نام واقعی (full_name) در صفحه عمومی نمایش داده نمی‌شود.
            foreach ([$member?->display_name, $member?->username] as $candidate) {
                if (trim((string) $candidate) !== '') {
                    return trim((string) $candidate);
                }
            }

            return 'عضو باشگاه';
        };
        $place = [1 => 'نفر اول', 2 => 'نفر دوم', 3 => 'نفر سوم'];
        $medal = [1 => '🏆', 2 => '🥈', 3 => '🥉'];
        $race = $yearRace;
        $progressRow = static fn (int $value, int $target): int => $target > 0 ? (int) min(100, round($value * 100 / $target)) : 100;
        $prizeLine = static function (array $row): string {
            $parts = [];
            if ($row['points'] > 0) $parts[] = persian_number($row['points']) . ' امتیاز';
            if ($row['coupon_percent'] > 0) $parts[] = 'کد تخفیف ' . $row['coupon_percent'] . '٪';
            if ($row['raffle_tickets'] > 0) $parts[] = persian_number($row['raffle_tickets']) . ' کد قرعه‌کشی';

            return implode(' + ', $parts) ?: '—';
        };
    @endphp

    <section class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:py-8">
        {{-- Hero --}}
        <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950 p-6 text-white shadow-2xl sm:p-8">
            <div class="absolute -right-16 -top-20 -z-10 h-72 w-72 rounded-full bg-amber-400/25 blur-3xl"></div>
            <div class="absolute -bottom-24 left-10 -z-10 h-72 w-72 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/30 bg-white/10 px-3 py-1.5 text-[11px] font-extrabold text-amber-200">
                        👑 تالار افتخار Cardiware
                    </div>
                    <h1 class="mt-4 text-2xl font-black leading-10 sm:text-4xl">قهرمانان باشگاه</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        هر ماه، هر فصل و هر سال، سه عضو برتر باشگاه با بیشترین XP انتخاب می‌شوند و جایزه می‌گیرند:
                        امتیاز، کد تخفیف شخصی و کد قرعه‌کشی. خرید محصولات، اشتراک ویژه و فروش با لینک همکاری بیشترین XP را می‌دهند.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('loyalty.leaderboard') }}" class="inline-flex h-11 items-center rounded-2xl bg-amber-400 px-5 text-xs font-black text-slate-950 transition hover:bg-amber-300">رقابت این ماه</a>
                        <a href="{{ auth()->check() ? route('account.loyalty') : route('login') }}" class="inline-flex h-11 items-center rounded-2xl border border-white/20 bg-white/10 px-5 text-xs font-black text-white transition hover:bg-white/20">{{ auth()->check() ? 'باشگاه من' : 'ورود و شروع رقابت' }}</a>
                    </div>
                </div>

                {{-- Year race --}}
                <div class="w-full rounded-3xl border border-amber-300/30 bg-white/10 p-5 backdrop-blur lg:max-w-md">
                    <div class="text-[11px] font-extrabold text-amber-200">رقابت کاربر برتر {{ $race['period']->label }}</div>
                    <div class="mt-2 text-lg font-black">
                        {{ $race['grandPrize'] !== '' ? '🎁 جایزه ویژه نفر اول: ' . $race['grandPrize'] : 'جایزه ویژه نفر اول به‌زودی اعلام می‌شود' }}
                    </div>
                    <p class="mt-2 text-[11px] leading-6 text-slate-300">
                        حد نصاب شرکت در رقابت سال: حداقل {{ persian_number($race['minXp']) }} XP،
                        {{ persian_number($race['minMissions']) }} مأموریت کامل‌شده و {{ persian_number($race['minOrders']) }} خرید در طول سال.
                    </p>

                    @if($race['me'])
                        <div class="mt-4 space-y-2.5">
                            @foreach([
                                ['XP امسال', $race['me']['xp'], $race['minXp']],
                                ['مأموریت‌های امسال', $race['me']['missions'], $race['minMissions']],
                                ['خریدهای امسال', $race['me']['orders'], $race['minOrders']],
                            ] as [$label, $value, $target])
                                @php($percent = $progressRow((int) $value, (int) $target))
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold text-slate-300">
                                        <span>{{ $label }}</span>
                                        <span>{{ persian_number($value) }} / {{ persian_number($target) }} {{ $percent >= 100 ? '✓' : '' }}</span>
                                    </div>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-emerald-400' : 'bg-amber-400' }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($race['leaders']->isNotEmpty())
                        <div class="mt-4 border-t border-white/10 pt-3 text-[11px] font-bold text-slate-300">پیشتازان امسال</div>
                        <ol class="mt-2 space-y-1.5">
                            @foreach($race['leaders'] as $account)
                                <li class="flex items-center justify-between gap-2 text-xs">
                                    <a href="{{ route('loyalty.members.show', $account->user_id) }}" class="truncate font-black hover:text-amber-200">{{ $medal[$account->leaderboard_rank] ?? '' }} {{ $nameFor($account->user) }}</a>
                                    <span class="shrink-0 text-amber-200">{{ persian_number($account->leaderboard_score) }} XP</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </section>

        {{-- Prizes per rank --}}
        <section class="grid gap-3 md:grid-cols-3">
            @foreach(['month' => 'برترین‌های ماه', 'season' => 'برترین‌های فصل', 'year' => 'برترین‌های سال'] as $type => $label)
                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">جوایز {{ $label }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach($prizes[$type] as $index => $row)
                            <li class="flex items-start gap-2 text-xs leading-6 text-slate-600 dark:text-slate-300">
                                <span class="shrink-0">{{ $medal[$index + 1] }}</span>
                                <span><b class="text-slate-800 dark:text-slate-100">{{ $place[$index + 1] }}:</b> {{ $prizeLine($row) }}
                                    @if($type === 'year' && $index === 0 && $race['grandPrize'] !== '')
                                        + <b class="text-amber-600 dark:text-amber-300">{{ $race['grandPrize'] }}</b>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </section>

        {{-- Winners --}}
        @foreach([
            ['title' => 'کاربران برتر سال', 'items' => $years, 'empty' => 'اولین کاربر برتر سال، پایان امسال معرفی می‌شود.'],
            ['title' => 'برترین‌های فصل', 'items' => $seasons, 'empty' => 'برترین‌های فصل، پایان هر فصل اینجا ثبت می‌شوند.'],
            ['title' => 'برترین‌های ماه', 'items' => $months, 'empty' => 'برترین‌های ماه، پایان هر ماه اینجا ثبت می‌شوند.'],
        ] as $group)
            <section>
                <h2 class="mb-3 text-lg font-black text-slate-950 dark:text-white">{{ $group['title'] }}</h2>
                @if($group['items']->isEmpty())
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-center text-xs font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">{{ $group['empty'] }}</div>
                @else
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($group['items'] as $closure)
                            <article class="rounded-3xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-white p-5 shadow-sm dark:border-amber-500/20 dark:from-amber-500/10 dark:via-slate-900 dark:to-slate-900">
                                <div class="text-[11px] font-extrabold text-amber-700 dark:text-amber-300">{{ $closure->period_label }}</div>
                                <ol class="mt-3 space-y-2.5">
                                    @foreach($closure->champions as $champion)
                                        <li class="flex items-center gap-3">
                                            <span class="text-xl">{{ $champion->trophy() }}</span>
                                            <img src="{{ $champion->user?->profileAvatarUrl() ?: asset('img/user-avatar.png') }}" alt="" class="h-9 w-9 rounded-xl object-cover">
                                            <div class="min-w-0 flex-1">
                                                <a href="{{ route('loyalty.members.show', $champion->user_id) }}" class="block truncate text-sm font-black text-slate-900 hover:text-brand-600 dark:text-white">{{ $nameFor($champion->user) }}</a>
                                                <div class="text-[10px] font-bold text-slate-400">{{ persian_number($champion->score) }} XP</div>
                                            </div>
                                            @if($champion->grand_prize)
                                                <span class="rounded-lg bg-amber-400 px-2 py-1 text-[9px] font-black text-slate-950">🎁 {{ $champion->grand_prize }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        {{-- Elite club --}}
        @if($eliteLevel)
            <section id="elite" class="relative overflow-hidden rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50 via-white to-fuchsia-50 p-6 dark:border-violet-500/20 dark:from-violet-500/10 dark:via-slate-900 dark:to-fuchsia-500/10">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="text-[11px] font-extrabold text-violet-700 dark:text-violet-300">💎 کانون نخبگان باشگاه</div>
                        <h2 class="mt-1 text-lg font-black text-slate-950 dark:text-white">در ساخت محصولات آینده نظر بده</h2>
                        <p class="mt-2 max-w-2xl text-xs leading-7 text-slate-600 dark:text-slate-300">
                            اعضای سطح «{{ $eliteLevel->badge }} {{ $eliteLevel->title }}» به بالا عضو کانون نخبگان‌اند: در نظرسنجی‌های ویژه
                            درباره‌ی پچ‌ها و مادهای بعدی رأی می‌دهند و نشان کانون روی پروفایلشان دیده می‌شود.
                        </p>
                    </div>
                    <span class="shrink-0 rounded-2xl px-4 py-2 text-xs font-black {{ $isEliteMember ? 'bg-violet-600 text-white' : 'bg-white text-violet-700 ring-1 ring-violet-200 dark:bg-slate-900 dark:text-violet-300 dark:ring-violet-500/30' }}">
                        {{ $isEliteMember ? 'شما عضو کانون هستید ✓' : 'از سطح ' . $eliteLevel->title . ' عضو می‌شوید' }}
                    </span>
                </div>
                @if($elitePolls->isNotEmpty())
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach($elitePolls as $poll)
                            <a href="{{ route('polls.show', $poll->slug) }}" class="rounded-2xl border border-violet-200 bg-white px-4 py-3 text-sm font-black text-slate-800 transition hover:border-violet-400 dark:border-violet-500/20 dark:bg-slate-900 dark:text-slate-100">🗳️ {{ $poll->title }}</a>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </section>
@endsection
