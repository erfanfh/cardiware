@extends('front.account.layout')

@section('page-content')
    @php
        use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
        use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
        use App\Domain\Loyalty\Enums\RewardTypeEnum;
        use App\Domain\Promotion\Enums\CouponTypeEnum;
        use App\Domain\Raffle\Models\DrawCampaign;
        use App\Domain\Raffle\Models\TicketGrant;
        use Illuminate\Support\Str;

        $level = $club['level'];
        $levelModel = $level['model'];
        $colors = $level['colors'];
        $ladder = $level['ladder'];
        $multiplierLabel = rtrim(rtrim(number_format($level['multiplier'], 2, '.', ''), '0'), '.');
        $nextLevelModel = $ladder->first(fn ($lvl) => $lvl->min_xp > $level['xp']);
        $nextColors = $nextLevelModel?->colorClasses();
        $closeToNext = $level['next'] !== null && $level['progressPercent'] >= 70;
        $barColors = $closeToNext && $nextColors !== null ? $nextColors : $colors;
    @endphp

    <div class="space-y-6 [&_button]:cursor-pointer">
        @if(session('success'))
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold leading-7 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold leading-7 text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- ===================== Hero: XP + Level + Balance ===================== --}}
        <x-front.cart class="relative overflow-hidden !p-0 dark:border-slate-700 dark:bg-slate-900">
            <div
                class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-amber-50/70 dark:from-slate-900 dark:via-slate-900 dark:to-brand-950/20"></div>
            <div class="mw-grid-overlay"></div>
            <div
                class="absolute -left-16 -top-16 h-44 w-44 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-900/20"></div>
            <div
                class="absolute -bottom-20 right-10 h-48 w-48 rounded-full bg-amber-200/50 blur-3xl dark:bg-amber-900/10"></div>

            <div class="relative p-5 sm:p-6 lg:p-8">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-7">
                        <div class="flex flex-wrap items-center gap-2">
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-3 py-1.5 text-xs font-extrabold text-brand-700 backdrop-blur dark:border-brand-900/60 dark:bg-slate-900/70 dark:text-brand-300">
                                باشگاه مخاطبان مودینگ وی
                            </div>

                            <div
                                class="inline-flex items-center gap-2 rounded-full border {{ $colors['chip'] }} py-1.5 pl-4 pr-1.5 text-xs font-extrabold backdrop-blur">
                                @if($levelModel->badge)
                                    <span
                                        class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gradient-to-br {{ $colors['ring'] }} text-base shadow-sm ring-2 ring-white/80 dark:ring-slate-900/80">
                                        <span class="drop-shadow-sm">{{ $levelModel->badge }}</span>
                                    </span>
                                @endif
                                سطح {{ $level['number'] }} — {{ $level['title'] }}
                            </div>

                            @if($level['multiplier'] > 1)
                                <div
                                    class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300/50 bg-emerald-50/80 px-3 py-1.5 text-xs font-extrabold text-emerald-700 backdrop-blur dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    ×{{ $multiplierLabel }} امتیاز خرید
                                </div>
                            @endif
                        </div>

                        <h1 class="mt-4 text-xl font-black leading-9 text-slate-900 sm:text-2xl dark:text-slate-100">
                            امتیازت رو به تجربه های جذاب تبدیل کن
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                            با فعالیت در مودینگ وی امتیاز بگیر، سطح ببر و پاداش دریافت کن.
                        </p>

                        {{-- XP progress --}}
                        <div class="mt-5 max-w-xl">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                <span>{{ number_format($level['xp']) }} XP</span>
                                @if($level['next'])
                                    <span>{{ number_format($level['nextMin']) }} XP</span>
                                @endif
                            </div>

                            <div
                                class="relative mt-1.5 h-3.5 overflow-hidden rounded-full bg-slate-200/80 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                <div data-loyalty-bar
                                     data-width="{{ $level['progressPercent'] }}"
                                     class="relative h-full w-0 overflow-hidden rounded-full bg-gradient-to-l {{ $barColors['bar'] }} transition-all duration-1000 ease-out">
                                    <span
                                        class="mw-bar-shine pointer-events-none absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-transparent via-white/50 to-transparent"></span>
                                </div>
                            </div>

                            <div class="mt-2 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                                @if($level['next'])
                                    @if($closeToNext)
                                        <span class="inline-flex items-center gap-1 animate-pulse {{ $barColors['text'] }}">
                                            فقط {{ number_format($level['xpToNext']) }} XP تا سطح {{ $level['nextTitle'] }} — تقریبا رسیدی! 🔥
                                        </span>
                                    @else
                                        <span class="{{ $colors['text'] }}">{{ number_format($level['xpToNext']) }} XP</span>
                                        تا سطح {{ $level['nextTitle'] }}
                                    @endif
                                @else
                                    <span class="text-cyan-700 dark:text-cyan-300">بالاترین سطح باشگاه؛ آفرین!</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('loyalty.leaderboard') }}"
                           class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 text-xs font-extrabold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-brand-600 dark:bg-white dark:text-slate-900 dark:shadow-black/20 dark:hover:bg-brand-300">
                            مشاهده برترین‌های باشگاه
                            <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                        </a>
                    </div>

                    <div class="lg:col-span-5">
                        <div
                            class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-950/60">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-bold text-slate-500 dark:text-slate-400">امتیاز قابل استفاده</div>
                                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 [&_svg]:h-5 [&_svg]:w-5">
                                    @include('icons.medal-06')
                                </span>
                            </div>

                            <div class="mt-2 flex items-end gap-2">
                                <div
                                    class="text-3xl font-black tracking-tight text-brand-700 dark:text-brand-300">{{ number_format($club['spendablePointBalance']) }}</div>
                                <div class="pb-1 text-xs font-bold text-slate-400">امتیاز</div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-bold">
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    معادل {{ number_format((int) floor($club['spendablePointBalance'] / 100) * 2000) }} تومان اعتبار
                                </span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    کل کسب: {{ number_format($club['lifetimeEarned']) }}
                                </span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    کل مصرف: {{ number_format($club['lifetimeSpent']) }}
                                </span>
                            </div>

                            @if($club['pointBalance'] < 0)
                                <div
                                    class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs leading-6 text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                                    موجودی حساب شما {{ number_format(abs($club['pointBalance'])) }} امتیاز بدهکار است.
                                    امتیازهای بعدی ابتدا این بدهی را پوشش می دهند.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-front.cart>

        @include('front.account.partials.celebration-overlay')

        {{-- ===================== Tabs ===================== --}}
        <div x-data="{ tab: (location.hash || '#overview').replace('#', '') }"
             @hashchange.window="tab = (location.hash || '#overview').replace('#', '')"
             class="space-y-5">

            <div
                class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-900 [&_button]:cursor-pointer">
                @foreach([
                    ['key' => 'overview', 'label' => 'نمای کلی', 'icon' => 'icons.dashboard-square-02'],
                    ['key' => 'rewards', 'label' => 'جوایز', 'icon' => 'icons.discount'],
                    ['key' => 'how-to-earn', 'label' => 'چطور امتیاز بگیرم؟', 'icon' => 'icons.chart-03'],
                    ['key' => 'point-history', 'label' => 'تاریخچه امتیاز', 'icon' => 'icons.invoice-03'],
                    ['key' => 'levels', 'label' => 'سطوح', 'icon' => 'icons.medal-06'],
                    ['key' => 'missions', 'label' => 'مأموریت‌ها', 'icon' => 'icons.target-01'],
                    ['key' => 'badges', 'label' => 'نشان‌ها', 'icon' => 'icons.checkmark-badge-01'],
                    ['key' => 'invite', 'label' => 'دعوت دوستان', 'icon' => 'icons.user-multiple-02'],
                ] as $tabDef)
                    <button type="button"
                            data-loyalty-tab="{{ $tabDef['key'] }}"
                            @click="tab = '{{ $tabDef['key'] }}'; history.replaceState(null, '', '#{{ $tabDef['key'] }}')"
                            :class="tab === '{{ $tabDef['key'] }}' ? 'border-brand-500/40 bg-gradient-to-b from-brand-400/15 to-brand-600/10 text-slate-900 dark:text-white' : 'border-transparent text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/70'"
                            class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-xs font-extrabold transition [&_svg]:h-4 [&_svg]:w-4">
                        @include($tabDef['icon'])
                        {{ $tabDef['label'] }}
                        @if(!empty($tabDef['soon']))
                            <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-black text-slate-400 dark:bg-slate-800 dark:text-slate-500">به‌زودی</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- ============ Tab: Overview ============ --}}
            <div x-show="tab === 'overview'" class="space-y-5">
                {{-- Streak card --}}
                <div
                    class="relative overflow-hidden rounded-3xl border border-amber-300/50 bg-gradient-to-l from-amber-50 via-white to-white p-5 shadow-sm dark:border-amber-500/25 dark:from-amber-500/10 dark:via-slate-900 dark:to-slate-900">
                    <div class="pointer-events-none absolute -left-12 -top-12 h-36 w-36 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-500/10"></div>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="grid h-16 w-16 place-items-center rounded-3xl border border-amber-300/60 bg-white text-3xl shadow-sm dark:border-amber-500/30 dark:bg-slate-950">
                                    🔥
                                </div>
                                @if($club['streak']['visitedToday'])
                                    <span class="absolute -bottom-1 -left-1 grid h-6 w-6 place-items-center rounded-full bg-emerald-500 text-[11px] font-black text-white">✓</span>
                                @endif
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">
                                        زنجیره ورود روزانه
                                    </div>
                                    <div class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-black text-amber-700 dark:text-amber-300" dir="ltr">
                                        {{ number_format($club['streak']['currentDays']) }} روز
                                    </div>
                                    @if($club['streak']['visitedToday'])
                                        <div class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                                            بازدید امروز ثبت شد
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-1.5 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                                    رکورد شما: {{ number_format($club['streak']['longestDays']) }} روز
                                    @if($club['streak']['nextMilestone'])
                                        · {{ number_format($club['streak']['nextMilestone']['days'] - $club['streak']['currentDays']) }} روز تا پاداش
                                        +{{ number_format($club['streak']['nextMilestone']['points']) }} امتیاز
                                    @endif
                                </div>

                                {{-- هفت خانه هفته --}}
                                <div class="mt-2.5 flex items-center gap-1.5" dir="ltr">
                                    @foreach(range(1, 7) as $day)
                                        <span
                                            class="h-2.5 w-6 rounded-full {{ $club['streak']['currentDays'] >= $day ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    @foreach([
                        ['label' => 'امتیاز فعلی', 'value' => $club['pointBalance'], 'suffix' => 'امتیاز', 'icon' => 'icons.medal-06', 'tone' => 'brand'],
                        ['label' => 'کل XP (سابقه)', 'value' => $level['xp'], 'suffix' => 'XP', 'icon' => 'icons.chart-03', 'tone' => 'emerald'],
                        ['label' => 'کل مصرف شده', 'value' => $club['lifetimeSpent'], 'suffix' => 'امتیاز', 'icon' => 'icons.tag-02', 'tone' => 'cyan'],
                        ['label' => 'پاداش های دریافتی', 'value' => $club['completedRewardCount'], 'suffix' => 'مورد', 'icon' => 'icons.checkmark-badge-01', 'tone' => 'rose'],
                    ] as $stat)
                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-2">
                                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div>

                                <span
                                    class="grid h-8 w-8 shrink-0 place-items-center rounded-xl
                                    @if($stat['tone'] === 'brand') bg-brand-500/10 text-brand-600 dark:text-brand-400
                                    @elseif($stat['tone'] === 'emerald') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                                    @elseif($stat['tone'] === 'cyan') bg-cyan-500/10 text-cyan-600 dark:text-cyan-400
                                    @else bg-rose-500/10 text-rose-600 dark:text-rose-400 @endif [&_svg]:h-4 [&_svg]:w-4">
                                    @include($stat['icon'])
                                </span>
                            </div>

                            <div class="mt-2 flex items-end gap-1.5">
                                <div
                                    class="text-xl font-black {{ $stat['value'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-slate-100' }}">{{ number_format($stat['value']) }}</div>
                                <div class="pb-0.5 text-[10px] font-bold text-slate-400">{{ $stat['suffix'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(!empty($club['monthlySummary']))
                    @php
                        $peakMonth = max(1, max(array_map(fn($m) => max($m['earned'], $m['spent']), $club['monthlySummary'])))
                    @endphp
                    <section
                        class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-black text-slate-900 dark:text-slate-100">گردش امتیاز ماه‌های اخیر</h2>
                                <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                                    مقایسه کسب و مصرف امتیاز در چهار ماه گذشته.
                                </p>
                            </div>

                            <div class="flex items-center gap-3 text-[10px] font-bold">
                                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    کسب
                                </span>
                                <span class="flex items-center gap-1.5 text-rose-500 dark:text-rose-400">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                    مصرف
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-4 gap-2 sm:gap-4">
                            @foreach($club['monthlySummary'] as $month)
                                <div class="flex flex-col items-center gap-2">
                                    <div class="flex h-28 w-full items-end justify-center gap-1.5">
                                        <div class="flex w-5 flex-col justify-end sm:w-7" title="کسب: {{ number_format($month['earned']) }}">
                                            <div
                                                class="w-full rounded-t-lg bg-gradient-to-t from-emerald-600 to-emerald-400 transition-all duration-700"
                                                style="height: {{ max(4, (int) round($month['earned'] / $peakMonth * 100)) }}%"></div>
                                        </div>
                                        <div class="flex w-5 flex-col justify-end sm:w-7" title="مصرف: {{ number_format($month['spent']) }}">
                                            <div
                                                class="w-full rounded-t-lg bg-gradient-to-t from-rose-600 to-rose-400 transition-all duration-700"
                                                style="height: {{ max(4, (int) round($month['spent'] / $peakMonth * 100)) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ $month['label'] }}</div>
                                    <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($month['earned']) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @include('front.account.partials.loyalty-point-conversion')
            </div>

            {{-- ============ Tab: Rewards ============ --}}
            <section id="rewards" x-show="tab === 'rewards'" class="scroll-mt-24" x-cloak>
                <div class="mb-4">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">پاداش های قابل دریافت</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">امتیازت را خرج کن؛ تخفیف بگیر
                        یا شانس حضور در قرعه کشی ها را بیشتر کن.</p>
                </div>

                @if($club['rewardOffers']->isEmpty())
                    <div
                        class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                        <div class="text-sm font-black text-slate-800 dark:text-slate-200">فعلا پاداش فعالی نداریم</div>
                        <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">به زودی گزینه های جدیدی برای
                            خرج کردن امتیاز اضافه می شود.</p>
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($club['rewardOffers'] as $rewardOffer)
                            @php
                                $availability = $club['rewardAvailabilities']->get($rewardOffer->getKey());
                                $isRaffle = $rewardOffer->type === RewardTypeEnum::RAFFLE_TICKET;
                                $campaign = $isRaffle && $rewardOffer->target instanceof DrawCampaign ? $rewardOffer->target : null;
                                $ticketQuantity = (int)($rewardOffer->config['ticket_quantity'] ?? 0);
                                $missingPoints = (int) data_get($availability?->context, 'missing_points', 0);
                                $couponType = data_get($rewardOffer->config, 'coupon_type');
                                $affordPercent = min(100, (int) round($club['spendablePointBalance'] / max(1, $rewardOffer->points_cost) * 100));
                            @endphp

                            <article
                                class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                                <div
                                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-400 via-brand-500 to-amber-400"></div>
                                <div class="p-5 sm:p-6">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="rounded-full border border-brand-100 bg-brand-50 px-2.5 py-1 text-[10px] font-extrabold text-brand-700 dark:border-brand-900/60 dark:bg-brand-950/30 dark:text-brand-300">
                                                {{ $rewardOffer->type->label() }}
                                            </span>
                                                @if($isRaffle && $campaign)
                                                    <span
                                                        class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300">
                                                    {{ $campaign->status->label() }}
                                                </span>
                                                @endif
                                            </div>

                                            <h3 class="mt-3 text-base font-black leading-7 text-slate-900 dark:text-slate-100">{{ $rewardOffer->title }}</h3>
                                            @if($rewardOffer->description)
                                                <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $rewardOffer->description }}</p>
                                            @endif
                                        </div>

                                        <div
                                            class="shrink-0 rounded-2xl bg-brand-50 px-3 py-2 text-center dark:bg-brand-950/30">
                                            <div
                                                class="text-lg font-black text-brand-700 dark:text-brand-300">{{ number_format($rewardOffer->points_cost) }}</div>
                                            <div class="text-[9px] font-bold text-brand-500">امتیاز</div>
                                        </div>
                                    </div>

                                    @if($isRaffle && $campaign)
                                        <a href="{{ route('raffles.show', $campaign->slug) }}"
                                           class="mt-4 block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-brand-200 hover:bg-brand-50/40 dark:border-slate-700 dark:bg-slate-950/50 dark:hover:border-brand-900/70">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-[10px] font-bold text-slate-400">قرعه کشی مقصد</div>
                                                    <div
                                                        class="mt-1 truncate text-sm font-black text-slate-800 dark:text-slate-200">{{ $campaign->title }}</div>
                                                </div>
                                                <div
                                                    class="shrink-0 rounded-xl bg-white px-3 py-2 text-center shadow-sm dark:bg-slate-900">
                                                    <div
                                                        class="text-base font-black text-amber-600 dark:text-amber-400">{{ number_format($ticketQuantity) }}</div>
                                                    <div class="text-[9px] font-bold text-slate-400">بلیط</div>
                                                </div>
                                            </div>
                                        </a>
                                    @elseif(!$isRaffle)
                                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                            <div class="rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-950/50">
                                                <div class="text-[10px] text-slate-400">مقدار تخفیف</div>
                                                <div class="mt-1 font-black text-slate-800 dark:text-slate-200">
                                                    @if($couponType === CouponTypeEnum::PERCENTAGE->value)
                                                        {{ number_format((int)data_get($rewardOffer->config, 'coupon_value')) }}٪
                                                    @else
                                                        {{ number_format((int)data_get($rewardOffer->config, 'coupon_value')) }} تومان
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-950/50">
                                                <div class="text-[10px] text-slate-400">اعتبار</div>
                                                <div class="mt-1 font-black text-slate-800 dark:text-slate-200">
                                                    {{ data_get($rewardOffer->config, 'expires_after_days') ? number_format((int)data_get($rewardOffer->config, 'expires_after_days')) . ' روز' : 'بدون انقضا' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!$availability?->canRedeem && $missingPoints > 0)
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                                <span>پیشرفت تا این پاداش</span>
                                                <span class="text-slate-700 dark:text-slate-200">
                                                    {{ number_format($club['spendablePointBalance']) }} / {{ number_format($rewardOffer->points_cost) }}
                                                </span>
                                            </div>
                                            <div
                                                class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div
                                                    class="h-full rounded-full bg-gradient-to-l from-brand-400 to-brand-600 transition-all duration-700"
                                                    style="width: {{ $affordPercent }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-5">
                                        @if($availability?->canRedeem)
                                            <form method="post"
                                                  action="{{ route('account.loyalty.rewards.redeem', $rewardOffer) }}">
                                                @csrf
                                                <input type="hidden" name="idempotency_key"
                                                       value="{{ (string) Str::uuid() }}">
                                                <button type="submit"
                                                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-b from-brand-400 to-brand-600 px-4 text-xs font-extrabold text-white shadow-[0_10px_28px_-10px_rgba(249,115,22,.7)] transition hover:from-brand-300 hover:to-brand-500 active:from-brand-500 active:to-brand-700">
                                                    {{ $isRaffle ? 'دریافت بلیط با امتیاز' : 'دریافت پاداش' }}
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" disabled
                                                    class="inline-flex h-11 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-slate-100 px-4 text-xs font-extrabold text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                                @if($missingPoints > 0)
                                                    {{ number_format($missingPoints) }} امتیاز دیگر لازم داریم
                                                @else
                                                    فعلا قابل دریافت نیست
                                                @endif
                                            </button>
                                            @if($availability?->reason && $missingPoints <= 0)
                                                <div
                                                    class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] leading-6 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300">
                                                    {{ $availability->reason }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                {{-- My rewards history --}}
                <div class="mt-8">
                    <h2 class="mb-4 text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">پاداش های من</h2>

                    <div class="space-y-3">
                        @forelse($club['rewardRedemptions'] as $redemption)
                            @php
                                $isReversed = $redemption->status === RewardRedemptionStatusEnum::REVERSED;
                                $fulfillment = $redemption->fulfillment;
                                $grant = $fulfillment instanceof TicketGrant ? $fulfillment : null;
                            @endphp
                            <div
                                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div
                                                class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $redemption->rewardTitle() }}</div>
                                            <span
                                                class="rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $isReversed ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300' }}">
                                            {{ $redemption->status->label() }}
                                        </span>
                                            @if($redemption->isRegistrationGift())
                                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-amber-700 dark:bg-amber-950/30 dark:text-amber-300">
                                            هدیه خوش‌آمدگویی
                                        </span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-400">
                                            @if($redemption->isRegistrationGift())
                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">بدون کسر امتیاز</span>
                                            @else
                                                <span>{{ number_format($redemption->points_spent) }} امتیاز</span>
                                            @endif
                                            <span>{{ jalali_date($redemption->created_at, 'Y/m/d H:i') }}</span>
                                            @if($redemption->coupon?->expires_at)
                                                <span>معتبر تا {{ jalali_date($redemption->coupon->expires_at, 'Y/m/d H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($redemption->coupon)
                                        <div x-data="{ copied: false }"
                                             class="flex items-center gap-2 rounded-xl bg-slate-50 p-2 dark:bg-slate-950/60">
                                            <code class="px-2 text-xs font-black text-slate-700 dark:text-slate-200"
                                                  dir="ltr">{{ $redemption->coupon->code }}</code>
                                            <button type="button"
                                                    x-on:click="navigator.clipboard.writeText(@js($redemption->coupon->code)); copied = true; setTimeout(() => copied = false, 1500)"
                                                    class="rounded-lg bg-white px-2.5 py-1.5 text-[10px] font-bold text-brand-600 shadow-sm dark:bg-slate-900 dark:text-brand-400">
                                                <span x-show="!copied">کپی</span><span x-show="copied" x-cloak>کپی شد</span>
                                            </button>
                                        </div>
                                    @elseif($grant)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div
                                                class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 dark:bg-amber-950/20 dark:text-amber-300">
                                                {{ number_format($grant->quantity) }} بلیط
                                            </div>
                                            <a href="{{ route('account.raffles') }}"
                                               class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-extrabold text-slate-700 transition hover:border-brand-200 hover:text-brand-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                                مشاهده بلیط ها
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($isReversed)
                                    <div
                                        class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] leading-6 text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-300">
                                        امتیاز این دریافت به حساب شما برگشت داده شده است.
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div
                                class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                                <div class="text-sm font-black text-slate-800 dark:text-slate-200">هنوز پاداشی دریافت نکردی
                                </div>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">از گزینه های بالا اولین پاداشت را
                                    انتخاب کن.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($club['rewardRedemptions']->hasPages())
                        <div class="mt-4">{{ $club['rewardRedemptions']->fragment('rewards')->links() }}</div>
                    @endif
                </div>
            </section>

            {{-- ============ Tab: How to earn ============ --}}
            <section id="how-to-earn" x-show="tab === 'how-to-earn'" class="scroll-mt-24" x-cloak>
                <div class="mb-4">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">چطور امتیاز بگیرم؟</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">با این فعالیت‌ها امتیاز جمع کن و
                        سریع‌تر به پاداش و سطح بعدی برس. امتیاز خرید با ضریب سطح
                        @if($level['multiplier'] > 1)
                            تو (×{{ $multiplierLabel }})
                        @endif
                        محاسبه می‌شود.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($club['earningRules'] as $rule)
                        @php
                            $cfg = $rule->config ?? [];
                            $ruleIcon = [
                                'registration' => 'icons.waving-hand-02',
                                'daily_visit' => 'icons.view',
                                'purchase' => 'icons.shopping-basket-03',
                                'comment' => 'icons.comment-01',
                                'membership_anniversary' => 'icons.calendar-03',
                                'time_spent' => 'icons.fire',
                            ][$rule->type->value] ?? null;
                        @endphp                        <div
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-brand-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-brand-500/40">
                            <div
                                class="pointer-events-none absolute -left-10 -top-10 h-24 w-24 rounded-full bg-brand-100/50 blur-2xl transition group-hover:bg-brand-200/60 dark:bg-brand-500/10"></div>

                            <div class="relative flex items-start gap-3">
                            <span
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-brand-500/25 bg-gradient-to-br from-brand-500/15 to-brand-500/5 text-brand-600 dark:text-brand-400 [&_svg]:h-5 [&_svg]:w-5">
                                @if($ruleIcon && view()->exists($ruleIcon))
                                    @include($ruleIcon)
                                @else
                                    <span class="text-lg">⚡</span>
                                @endif
                            </span>

                                <div class="min-w-0">
                                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $rule->title }}</div>
                                    <div class="mt-1.5 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        @switch($rule->type)
                                            @case(EarningRuleTypeEnum::PURCHASE)
                                                هر {{ number_format((int)($cfg['amount_step'] ?? 0)) }} تومان
                                                خرید، {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز پایه
                                                @if($level['multiplier'] > 1)
                                                    × ضریب سطح شما
                                                @endif
                                                @break
                                            @case(EarningRuleTypeEnum::COMMENT)
                                                هر دیدگاه تایید شده {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز
                                                @if(!empty($cfg['daily_limit']))
                                                    · حداکثر {{ number_format((int)$cfg['daily_limit']) }} بار در روز
                                                @endif
                                                @break
                                            @case(EarningRuleTypeEnum::DAILY_VISIT)
                                                {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز برای اولین بازدید روزانه
                                                @break
                                            @case(EarningRuleTypeEnum::REGISTRATION)
                                                {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز هدیه عضویت
                                                @break
                                            @case(EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY)
                                                {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز هدیه سالگرد عضویت
                                                @break
                                            @case(EarningRuleTypeEnum::TIME_SPENT)
                                                {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز برای {{ number_format((int)($cfg['minutes'] ?? 30)) }} دقیقه فعالیت
                                                @break
                                            @default
                                                با انجام این فعالیت امتیاز دریافت می کنی.
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ Tab: Point history ============ --}}
            <section id="point-history" x-show="tab === 'point-history'" class="scroll-mt-24" x-cloak>
                <div class="mb-4">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">تاریخچه امتیاز</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">تمام کسب، مصرف، برگشت و اصلاحات
                        امتیاز حساب شما.</p>
                </div>

                <div class="space-y-2">
                    @php
                        $printedMonth = null
                    @endphp
                    @forelse($club['pointEntries'] as $entry)
                        @php
                            $credit = $entry->direction->isCredit();
                            $entryMonth = jalali_date($entry->occurred_at ?? $entry->created_at, 'F Y');
                        @endphp

                        @if($entryMonth !== $printedMonth)
                            @php
                                $printedMonth = $entryMonth
                            @endphp
                            <div class="pt-2">
                                <div
                                    class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5 text-[11px] font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $entryMonth }}
                                </div>
                            </div>
                        @endif

                        <div
                            class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition hover:border-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-brand-500/30">
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $credit ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }} [&_svg]:h-4 [&_svg]:w-4">
                                    @if($credit)
                                        @include('icons.arrow-down-01')
                                    @else
                                        @include('icons.arrow-left-02')
                                    @endif
                                </span>

                                <div class="min-w-0">
                                    <div
                                        class="truncate text-xs font-extrabold text-slate-800 dark:text-slate-200">{{ $entry->description ?: $entry->type->label() }}</div>
                                    <div
                                        class="mt-1 text-[10px] text-slate-400">{{ jalali_date($entry->occurred_at ?? $entry->created_at, 'Y/m/d H:i') }}</div>
                                </div>
                            </div>

                            <div class="shrink-0 text-left">
                                <div
                                    class="text-sm font-black {{ $credit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}"
                                    dir="ltr">
                                    {{ $credit ? '+' : '-' }}{{ number_format((int)$entry->amount) }}
                                </div>
                                <div class="mt-1 text-[10px] text-slate-400">
                                    مانده {{ number_format((int)$entry->balance_after) }}</div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                            هنوز گردش امتیازی ثبت نشده است.
                        </div>
                    @endforelse
                </div>

                @if($club['pointEntries']->hasPages())
                    <div class="mt-4">{{ $club['pointEntries']->fragment('point-history')->links() }}</div>
                @endif
            </section>

            {{-- ============ Tab: Levels ============ --}}
            <section id="levels" x-show="tab === 'levels'" class="scroll-mt-24" x-cloak>
                <div class="mb-4">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">سطوح باشگاه</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        سطح فقط با XP (کل امتیاز کسب‌شده) بالا می‌رود و با خرج کردن امتیاز پایین نمی‌آید.
                    </p>
                </div>

                <div class="space-y-3">
                    @foreach($ladder as $i => $lvl)
                        @php
                            $lvlColors = $lvl->colorClasses()
                        @endphp
                        @php
                            $isCurrent = $lvl->is($levelModel)
                        @endphp
                        @php
                            $isReached = $level['xp'] >= $lvl->min_xp
                        @endphp
                        <div
                            class="relative overflow-hidden rounded-2xl border p-4 transition
                            @if($isCurrent) border-brand-400/50 bg-gradient-to-l from-brand-500/10 to-transparent shadow-[0_10px_30px_-15px_rgba(249,115,22,.5)] dark:from-brand-500/15 @else border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900 @endif">

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="grid h-12 w-12 shrink-0 place-items-center rounded-full text-xl @if($isReached) bg-gradient-to-br {{ $lvlColors['ring'] }} shadow-sm ring-2 ring-white/70 dark:ring-slate-900 @else border border-slate-200 bg-slate-50 opacity-60 dark:border-slate-700 dark:bg-slate-800 @endif">
                                        {{ $lvl->badge ?? '·' }}
                                    </div>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 text-sm font-black text-slate-900 dark:text-slate-100">
                                            سطح {{ $i + 1 }} — {{ $lvl->title }}
                                            @if($isCurrent)
                                                <span
                                                    class="rounded-full bg-brand-500 px-2.5 py-1 text-[9px] font-black text-white shadow-sm">سطح فعلی تو</span>
                                            @elseif($isReached)
                                                <span
                                                    class="grid h-5 w-5 place-items-center rounded-full bg-emerald-500/15 text-[10px] font-black text-emerald-600 dark:text-emerald-300">✓</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                            از {{ number_format($lvl->min_xp) }} XP
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-[10px] font-bold">
                                    @if($lvl->reward_points > 0)
                                        <span class="rounded-lg bg-emerald-500/10 px-2.5 py-1.5 text-emerald-700 dark:text-emerald-300">
                                            پاداش رسیدن: +{{ number_format($lvl->reward_points) }} امتیاز
                                        </span>
                                    @endif
                                    <span class="rounded-lg bg-cyan-500/10 px-2.5 py-1.5 text-cyan-700 dark:text-cyan-300">
                                        ضریب خرید ×{{ rtrim(rtrim(number_format($lvl->purchase_multiplier, 2, '.', ''), '0'), '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ Tab: Missions (weekly) ============ --}}
            <section id="missions" x-show="tab === 'missions'" class="scroll-mt-24" x-cloak>
                <div class="mb-4">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">مأموریت‌های این هفته</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        هر هفته (از شنبه) مأموریت‌ها تازه می‌شوند؛ جایزه با رسیدن به هدف خودکار پرداخت می‌شود.
                    </p>
                </div>

                @if($club['missions']->isEmpty())
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                        <div class="text-sm font-black text-slate-800 dark:text-slate-200">فعلا مأموریت فعالی وجود ندارد</div>
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($club['missions'] as $missionData)
                            @php
                                $mission = $missionData['mission'];
                                $isCompleted = $missionData['completedAt'] !== null;
                            @endphp
                            <div
                                class="relative overflow-hidden rounded-3xl border p-5 transition
                                @if($isCompleted) border-emerald-300/60 bg-gradient-to-l from-emerald-500/10 to-transparent dark:border-emerald-500/30 @else border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900 @endif">

                                @if($isCompleted)
                                    <span class="absolute top-0 left-0 h-full w-1.5 bg-gradient-to-b from-emerald-400 to-emerald-600"></span>
                                @endif

                                <div class="flex items-start gap-3">
                                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full border border-brand-500/25 bg-gradient-to-br from-brand-500/15 to-brand-500/5 text-2xl">
                                        {{ $mission->icon ?? '🎯' }}
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 text-sm font-black text-slate-900 dark:text-slate-100">
                                                {{ $mission->title }}
                                                @if($isCompleted)
                                                    <span
                                                        class="rounded-full bg-emerald-500 px-2.5 py-1 text-[9px] font-black text-white shadow-sm">کامل شد</span>
                                                @endif
                                            </div>
                                            <div class="shrink-0 rounded-xl bg-brand-50 px-2.5 py-1 text-[11px] font-black text-brand-700 dark:bg-brand-950/30 dark:text-brand-300">
                                                +{{ number_format($mission->reward_points) }} امتیاز
                                            </div>
                                        </div>
                                        <div class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $mission->description }}</div>

                                        <div class="mt-3">
                                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                                <span>{{ $mission->metric->label() }}</span>
                                                <span class="text-slate-700 dark:text-slate-200" dir="ltr">
                                                    {{ number_format($missionData['progressCount']) }} / {{ number_format($missionData['targetCount']) }}
                                                </span>
                                            </div>
                                            <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div data-loyalty-bar
                                                     data-width="{{ $missionData['percent'] }}"
                                                     class="relative h-full w-0 overflow-hidden rounded-full transition-all duration-700 ease-out {{ $isCompleted ? 'bg-gradient-to-l from-emerald-400 to-emerald-600' : 'bg-gradient-to-l from-brand-400 to-brand-600' }}">
                                                    <span
                                                        class="mw-bar-shine pointer-events-none absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-transparent via-white/50 to-transparent"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ============ Tab: Badges ============ --}}
            <section id="badges" x-show="tab === 'badges'" class="scroll-mt-24" x-cloak>
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">نشان‌های افتخار</h2>
                        <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                            نشان‌ها با فعالیت واقعی باز می‌شوند؛ {{ number_format($club['achievements']['earnedCount']) }} از
                            {{ number_format($club['achievements']['totalCount']) }} نشان را داری.
                        </p>
                    </div>
                    <div class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $club['achievements']['earnedCount'] }} / {{ $club['achievements']['totalCount'] }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
                    @foreach($club['achievements']['all'] as $achievement)
                        @php
                            $earnedRow = $club['achievements']['earnedMap']->get($achievement->getKey());
                            $isEarned = $earnedRow !== null;
                        @endphp
                        <div
                            class="relative overflow-hidden rounded-2xl border p-4 text-center transition
                            @if($isEarned) border-amber-300/60 bg-gradient-to-b from-amber-50/80 to-white shadow-[0_10px_30px_-15px_rgba(245,158,11,.45)] dark:border-amber-500/30 dark:from-amber-500/10 dark:to-slate-900 @else border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900 @endif">

                            <div class="text-4xl {{ $isEarned ? '' : 'opacity-25 grayscale' }}">{{ $achievement->badge ?? '🏅' }}</div>
                            <div class="mt-2 text-sm font-black {{ $isEarned ? 'text-slate-900 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500' }}">{{ $achievement->title }}</div>
                            <div class="mt-1 text-[10px] leading-5 text-slate-500 dark:text-slate-400">{{ $achievement->description }}</div>

                            @if($achievement->reward_points > 0)
                                <div class="mt-2 inline-block rounded-lg px-2 py-1 text-[10px] font-black
                                    {{ $isEarned ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                    +{{ number_format($achievement->reward_points) }} امتیاز
                                </div>
                            @endif

                            @if($isEarned)
                                <div class="mt-2 text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                    {{ jalali_date($earnedRow->awarded_at, 'Y/m/d') }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ Tab: Invite friends ============ --}}
            <section id="invite" x-show="tab === 'invite'" class="scroll-mt-24" x-cloak>
                <div class="mb-4">
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">دعوت دوستان</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        لینک اختصاصی‌ات را با دوستانت به اشتراک بگذار؛ با ثبت‌نام و اولین خریدشان هدیه می‌گیری.
                    </p>
                </div>

                @if($affiliate === null)
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                        <div class="text-3xl">🤝</div>
                        <div class="mt-3 text-sm font-black text-slate-800 dark:text-slate-200">دعوت دوستان فعلا غیرفعال است</div>
                        <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                            سیستم معرفی به‌زودی فعال می‌شود؛ فعال که شد از همین تب خبردار می‌شوی.
                        </p>
                    </div>
                @else
                    {{-- لینک دعوت --}}
                    <div class="relative overflow-hidden rounded-3xl border border-brand-200/70 bg-gradient-to-l from-brand-50 via-white to-white p-5 shadow-sm dark:border-brand-500/25 dark:from-brand-950/25 dark:via-slate-900 dark:to-slate-900">
                        <div class="mw-grid-overlay"></div>
                        <div class="pointer-events-none absolute -left-12 -top-12 h-32 w-32 rounded-full bg-brand-200/40 blur-3xl dark:bg-brand-500/10"></div>

                        <div class="relative">
                            <div class="text-sm font-black text-slate-900 dark:text-slate-100">لینک اختصاصی دعوت شما</div>
                            <p class="mt-1 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                                دوستانت با این لینک ثبت‌نام کنند، به شما وصل می‌شوند.
                            </p>

                            <div class="mt-3 flex flex-col gap-2 sm:flex-row" x-data="{ copied: false }">
                                <div dir="ltr"
                                     class="flex h-11 min-w-0 flex-1 items-center overflow-x-auto rounded-2xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                                    {{ url('/') }}/?ref={{ $affiliate->code }}
                                </div>
                                <button type="button"
                                        @click="
                                            navigator.clipboard.writeText('{{ url('/') }}/?ref={{ $affiliate->code }}');
                                            copied = true;
                                            setTimeout(() => copied = false, 2000)
                                        "
                                        class="inline-flex h-11 shrink-0 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 text-xs font-extrabold text-white transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900">
                                    <span x-text="copied ? 'کپی شد ✓' : 'کپی لینک'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- پاداش‌های دعوت --}}
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-3xl border border-emerald-200/70 bg-white p-4 dark:border-emerald-500/25 dark:bg-slate-900">
                            <div class="flex items-center gap-2">
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-500/15 text-lg">🎁</span>
                                <div class="text-xs font-black text-slate-900 dark:text-slate-100">هدیه به تو</div>
                            </div>
                            <p class="mt-2 text-[11px] font-bold leading-6 text-slate-500 dark:text-slate-400">
                                بعد از اولین خرید هر دوستِ معرفی‌شده، پاداش امتیاز ویژه دعوت می‌گیری.
                            </p>
                        </div>
                        <div class="rounded-3xl border border-cyan-200/70 bg-white p-4 dark:border-cyan-500/25 dark:bg-slate-900">
                            <div class="flex items-center gap-2">
                                <span class="grid h-9 w-9 place-items-center rounded-full bg-cyan-500/15 text-lg">🎉</span>
                                <div class="text-xs font-black text-slate-900 dark:text-slate-100">هدیه به دوستت</div>
                            </div>
                            <p class="mt-2 text-[11px] font-bold leading-6 text-slate-500 dark:text-slate-400">
                                دوستت هم بعد از اولین خریدش با لینک تو، امتیاز هدیه اولین خرید می‌گیرد.
                            </p>
                        </div>
                    </div>

                    {{-- ورود به پنل همکاری در فروش --}}
                    <a href="{{ route('account.affiliate') }}"
                       class="mt-3 flex items-center justify-between gap-3 rounded-3xl border border-slate-200 bg-white p-4 transition hover:border-brand-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900 dark:hover:border-brand-500/40">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-brand-500/15 to-brand-500/5 text-xl">💼</span>
                            <div>
                                <div class="text-sm font-black text-slate-900 dark:text-slate-100">پنل همکاری در فروش</div>
                                <div class="mt-1 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                                    آمار کلیک و تبدیل، درآمد کمیسیون و درخواست برداشت — همه یکجا.
                                </div>
                            </div>
                        </div>
                        <span class="[&_svg]:h-5 [&_svg]:w-5 shrink-0 text-brand-600 dark:text-brand-400">@include('icons.arrow-left-02')</span>
                    </a>
                @endif
            </section>
        </div>
    </div>

    @push('scripts')
        @once
            <style>
                .mw-bar-shine { animation: mw-bar-shine 2.4s ease-in-out infinite; }
                @keyframes mw-bar-shine {
                    0% { transform: translateX(230%); }
                    60%, 100% { transform: translateX(-270%); }
                }
                @media (prefers-reduced-motion: reduce) {
                    .mw-bar-shine { animation: none; }
                    [data-loyalty-bar] { transition: none !important; }
                }
            </style>
        @endonce
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const map = { '#rewards': 'rewards', '#point-history': 'point-history', '#ways-to-earn': 'how-to-earn' };
                const hash = location.hash;
                if (map[hash]) {
                    history.replaceState(null, '', '#' + map[hash]);
                    window.dispatchEvent(new HashChangeEvent('hashchange'));
                }

                const animateBar = (bar) => {
                    const target = bar.dataset.width + '%';
                    bar.style.width = '0%';
                    requestAnimationFrame(() => requestAnimationFrame(() => {
                        bar.style.width = target;
                    }));
                };

                const animateBars = (root) => {
                    (root || document).querySelectorAll('[data-loyalty-bar]').forEach(animateBar);
                };

                const animateVisibleTabBars = () => {
                    setTimeout(() => {
                        document.querySelectorAll('section[id]').forEach((section) => {
                            if (section.offsetParent === null) return;
                            section.querySelectorAll('[data-loyalty-bar]').forEach((bar) => {
                                if (bar.style.width === bar.dataset.width + '%') return;
                                animateBar(bar);
                            });
                        });
                    }, 60);
                };

                animateBars(document);
                document.querySelectorAll('[data-loyalty-tab]').forEach((btn) => {
                    btn.addEventListener('click', animateVisibleTabBars);
                });
                window.addEventListener('hashchange', animateVisibleTabBars);
            });
        </script>
    @endpush
@endsection
