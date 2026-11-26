@php
    use App\Domain\Promotion\Enums\CouponTypeEnum;

    $availableCoupons = $audienceClub['availableCoupons'];
    $hasAvailableCoupons = $availableCoupons->isNotEmpty();
    $level = $user->loyalty_level ?? null;
@endphp

<section class="relative overflow-hidden rounded-[1.75rem] border border-amber-200/80 bg-gradient-to-l from-amber-50 via-white to-brand-50 p-4 shadow-sm sm:p-6 dark:border-amber-400/15 dark:from-amber-950/20 dark:via-slate-900 dark:to-brand-950/20">
    <div class="mw-grid-overlay"></div>
    <div class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-400/10"></div>
    <div class="pointer-events-none absolute -bottom-28 left-1/4 h-52 w-52 rounded-full bg-brand-300/20 blur-3xl dark:bg-brand-400/10"></div>

    <div class="relative grid gap-5 {{ $hasAvailableCoupons ? 'lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.9fr)] lg:items-stretch' : '' }}">
        <div class="flex min-w-0 flex-col justify-between gap-5 sm:flex-row sm:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-white/80 px-3 py-1.5 text-[11px] font-black text-amber-700 shadow-sm backdrop-blur dark:border-amber-400/20 dark:bg-slate-900/70 dark:text-amber-300 [&_svg]:h-4 [&_svg]:w-4">
                        @include('icons.award-03')
                        Community Club
                    </div>

                    @if($level)
                        @php
                            $cardLevelColors = $level->colorClasses()
                        @endphp
                        <div class="inline-flex items-center gap-1.5 rounded-full border {{ $cardLevelColors['chip'] }} py-1 pl-3 pr-1 text-[10px] font-black backdrop-blur">
                            @if($level->badge)
                                <span
                                    class="grid h-6 w-6 place-items-center rounded-full bg-gradient-to-br {{ $cardLevelColors['ring'] }} text-xs shadow-sm ring-2 ring-white/70 dark:ring-slate-900/80">
                                    <span class="drop-shadow-sm">{{ $level->badge }}</span>
                                </span>
                            @endif
                            Level {{ $level->title }}
                        </div>
                    @endif
                </div>

                <h2 class="mt-3 text-lg font-black leading-8 text-slate-950 sm:text-xl dark:text-white">
                    Turn your points into real rewards
                </h2>
                <p class="mt-1 max-w-2xl text-xs font-medium leading-6 text-slate-600 sm:text-sm dark:text-slate-300">
                    By being active on Cardiware Earn points, improve your rank and claim exclusive rewards.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2.5">
                    <a
                        href="{{ route('account.loyalty') }}"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-slate-950 px-4 text-xs font-black text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-brand-700 dark:bg-white dark:text-slate-950 dark:hover:bg-brand-100 [&_svg]:h-3.5 [&_svg]:w-3.5"
                    >
                        Enter club
                        <x-front.arrow-right-02/>
                    </a>
                    <a
                        href="{{ route('loyalty.leaderboard') }}"
                        class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white/80 px-4 text-xs font-black text-slate-700 transition hover:border-amber-300 hover:text-amber-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-amber-400/30 dark:hover:text-amber-300"
                    >
                        Leaderboard
                    </a>
                </div>
            </div>

            <a
                href="{{ route('account.loyalty') }}#point-history"
                class="group flex shrink-0 items-center gap-3 self-start rounded-2xl border border-amber-200 bg-white/85 p-3.5 shadow-sm backdrop-blur transition hover:-translate-y-0.5 hover:shadow-md sm:self-center dark:border-amber-400/20 dark:bg-slate-900/80"
                aria-label="View points history"
            >
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-amber-300 to-orange-500 text-white shadow-lg shadow-amber-500/20 [&_svg]:h-6 [&_svg]:w-6">
                    @include('icons.star')
                </span>
                <span>
                    <span class="block text-[10px] font-bold text-slate-500 dark:text-slate-400">Your points balance</span>
                    <span class="mt-0.5 block text-xl font-black text-slate-950 dark:text-white">
                        {{ number_format($audienceClub['pointBalance']) }}
                        <small class="text-[10px] font-bold text-amber-700 dark:text-amber-300">points</small>
                    </span>
                </span>
            </a>
        </div>

        @if($hasAvailableCoupons)
            <div class="rounded-2xl border border-emerald-200/80 bg-white/90 p-3.5 shadow-sm backdrop-blur dark:border-emerald-400/15 dark:bg-slate-900/85 sm:p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-black text-slate-900 dark:text-white">Rewards ready to use</div>
                        <div class="mt-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-300">Exclusive to your account</div>
                    </div>
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300 [&_svg]:h-5 [&_svg]:w-5">
                        @include('icons.discount')
                    </span>
                </div>

                <div class="mt-3 space-y-2.5">
                    @foreach($availableCoupons as $coupon)
                        <div
                            x-data="{ copied: false }"
                            class="flex flex-col gap-3 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/60 p-3 sm:flex-row sm:items-center sm:justify-between dark:border-emerald-400/20 dark:bg-emerald-400/5"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">
                                        @if($coupon->type === CouponTypeEnum::PERCENTAGE)
                                            {{ number_format($coupon->value) }}% off
                                        @else
                                            {{ number_format($coupon->value) }} toman discount
                                        @endif
                                    </span>
                                    @if($coupon->expires_at)
                                        <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                            until {{ (($coupon->expires_at) ? \Illuminate\Support\Carbon::parse($coupon->expires_at)->format('Y/m/d') : '') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 truncate text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $coupon->title }}
                                </div>
                            </div>

                            <button
                                type="button"
                                x-on:click="navigator.clipboard.writeText(@js($coupon->code)); copied = true; setTimeout(() => copied = false, 1600)"
                                class="flex h-9 shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 text-[11px] font-black text-emerald-700 shadow-sm transition hover:border-emerald-300 dark:border-emerald-400/20 dark:bg-slate-800 dark:text-emerald-300"
                                aria-label="Copy discount code {{ $coupon->code }}"
                            >
                                <code x-show="!copied" dir="ltr">{{ $coupon->code }}</code>
                                <span x-show="copied" x-cloak>Copied ✓</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
