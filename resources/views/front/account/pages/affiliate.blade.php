@extends('front.account.layout')

@section('page-content')
    @php
        $commissionPercent = $affiliate->commission_type === \App\Domain\Affiliate\Enums\AffiliateCommissionTypeEnum::PERCENT
            ? rtrim(rtrim(number_format($affiliate->commission_value, 2, '.', ''), '0'), '.')
            : null;
        $canWithdraw = $overview['availableBalance'] >= $affiliate->min_withdrawal_amount && $overview['pendingWithdrawalAmount'] === 0;
    @endphp

    <div class="space-y-6 [&_button]:cursor-pointer">
        @include('front.account.partials.celebration-overlay')

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold leading-7 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold leading-7 text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Commission increase announcement for content creators --}}
        <div class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-amber-500/25 dark:bg-amber-500/10">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 shrink-0 text-lg">📢</span>
                <p class="text-xs font-bold leading-6 text-amber-900 dark:text-amber-200">
                    <b class="font-black">Are you a content creator or do you have a large community?</b>
                    Your commission rate is not fixed; you can earn more as sales grow.
                    Submit a support ticket with the subject «Affiliate commission increase request» so your rate can be increased after review.
                </p>
            </div>
            <a href="{{ route('account.tickets') }}"
               class="shrink-0 self-start rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-black text-white transition hover:bg-amber-600 sm:self-auto">
                Send support ticket
            </a>
        </div>

        {{-- ===================== Hero ===================== --}}
        <x-front.cart class="relative overflow-hidden !p-0 dark:border-slate-700 dark:bg-slate-900">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-emerald-50/60 dark:from-slate-900 dark:via-slate-900 dark:to-emerald-950/20"></div>
            <div class="mw-grid-overlay"></div>
            <div class="absolute -left-16 -top-16 h-44 w-44 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-900/20"></div>
            <div class="absolute -bottom-20 right-10 h-48 w-48 rounded-full bg-emerald-200/40 blur-3xl dark:bg-emerald-900/10"></div>

            <div class="relative p-5 sm:p-6 lg:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-3 py-1.5 text-xs font-extrabold text-brand-700 backdrop-blur dark:border-brand-900/60 dark:bg-slate-900/70 dark:text-brand-300">
                        Affiliate dashboard
                    </div>
                    @php
                        $affiliateSettings = app(\App\Infrastructure\Services\Setting\Groups\AffiliateSettings::class);
                        $referralBonusPoints = $affiliateSettings->referralBonusReferrerPoints();
                    @endphp
                    @if($affiliateSettings->referralBonusEnabled() && $referralBonusPoints > 0)
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-amber-300/50 bg-amber-50/80 px-3 py-1.5 text-xs font-extrabold text-amber-700 backdrop-blur dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            🎁 {{ number_format($referralBonusPoints) }} club points after invited friend’s first purchase
                        </div>
                    @endif
                    @if($commissionPercent)
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300/50 bg-emerald-50/80 px-3 py-1.5 text-xs font-extrabold text-emerald-700 backdrop-blur dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            Commission {{ $commissionPercent }}% of each order
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300/50 bg-emerald-50/80 px-3 py-1.5 text-xs font-extrabold text-emerald-700 backdrop-blur dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            Commission {{ number_format((int) $affiliate->commission_value) }} toman per order
                        </div>
                    @endif
                </div>

                <h1 class="mt-4 text-xl font-black leading-9 text-slate-900 sm:text-2xl dark:text-slate-100">
                    Earn income from every referral
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                    Share your link or discount code {{ $affiliate->code }} and orders placed with them will be attributed to your account, with commission added immediately to your balance.
                </p>

                {{-- Link + Code --}}
                <div class="mt-5 grid gap-3 lg:grid-cols-2">
                    <div class="rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-950/60" x-data="{ copied: false }">
                        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Referral link</div>
                        <div class="mt-2 flex items-center gap-2">
                            <div dir="ltr" class="h-10 min-w-0 flex-1 overflow-x-auto rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-bold leading-10 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                {{ $referralUrl }}
                            </div>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $referralUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="h-10 shrink-0 rounded-xl bg-slate-900 px-3 text-[11px] font-extrabold text-white transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900">
                                <span x-text="copied ? 'Copy ✓' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-950/60" x-data="{ copied: false }">
                        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Affiliate discount code (for checkout)</div>
                        <div class="mt-2 flex items-center gap-2">
                            <div dir="ltr" class="h-10 min-w-0 flex-1 overflow-x-auto rounded-xl border border-dashed border-brand-300 bg-brand-50/50 px-3 text-sm font-black leading-10 tracking-widest text-brand-700 dark:border-brand-500/40 dark:bg-brand-950/20 dark:text-brand-300">
                                {{ $affiliate->code }}
                            </div>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $affiliate->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="h-10 shrink-0 rounded-xl bg-brand-500 px-3 text-[11px] font-extrabold text-white transition hover:-translate-y-0.5">
                                <span x-text="copied ? 'Copy ✓' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-front.cart>

        {{-- ===================== Balance ===================== --}}
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-emerald-200/70 bg-gradient-to-l from-emerald-50 to-white p-4 shadow-sm dark:border-emerald-500/25 dark:from-emerald-950/25 dark:to-slate-900">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Withdrawable</div>
                <div class="mt-1.5 text-xl font-black text-emerald-700 dark:text-emerald-300">{{ number_format($overview['availableBalance']) }} <span class="text-[10px]">Toman</span></div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Total active commission</div>
                <div class="mt-1.5 text-xl font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['totalCommission']) }} <span class="text-[10px]">Toman</span></div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Paid</div>
                <div class="mt-1.5 text-xl font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['paidOut']) }} <span class="text-[10px]">Toman</span></div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Pending review</div>
                <div class="mt-1.5 text-xl font-black text-amber-600 dark:text-amber-300">{{ number_format($overview['pendingWithdrawalAmount']) }} <span class="text-[10px]">Toman</span></div>
            </div>
        </div>

        {{-- ===================== Stats ===================== --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="text-sm font-black text-slate-900 dark:text-slate-100">Your link performance</div>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-950/50">
                    <div class="text-lg font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['clicksCount']) }}</div>
                    <div class="mt-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">Clicks</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-950/50">
                    <div class="text-lg font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['signupsCount']) }}</div>
                    <div class="mt-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">Registrations</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-950/50">
                    <div class="text-lg font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['buyersCount']) }}</div>
                    <div class="mt-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">Buyers</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-950/50">
                    <div class="text-lg font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['ordersCount']) }}</div>
                    <div class="mt-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">Order</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-950/50">
                    <div class="text-lg font-black text-brand-600 dark:text-brand-300">{{ rtrim(rtrim(number_format($overview['conversionPercent'], 1), '0'), '.') }}%</div>
                    <div class="mt-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">Conversion rate</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-950/50">
                    <div class="text-lg font-black text-slate-900 dark:text-slate-100">{{ number_format($overview['revenue']) }}</div>
                    <div class="mt-1 text-[10px] font-bold text-slate-500 dark:text-slate-400">Purchase turnover (toman)</div>
                </div>
            </div>
        </div>

        {{-- ===================== Orders + Withdrawal ===================== --}}
        <div class="grid gap-4 lg:grid-cols-5">
            {{-- Attributed orders --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-3 dark:border-slate-700 dark:bg-slate-900">
                <div class="text-sm font-black text-slate-900 dark:text-slate-100">Orders attributed to you</div>

                <div class="mt-4 space-y-2">
                    @forelse($overview['recentOrders'] as $affiliateOrder)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                            <div class="min-w-0">
                                <div class="truncate text-xs font-black text-slate-800 dark:text-slate-200">
                                    {{ $affiliateOrder->user?->first_name }} {{ $affiliateOrder->user?->last_name }}
                                    <span class="mr-1 text-[10px] font-bold text-slate-400" dir="ltr">#{{ $affiliateOrder->order?->order_number ?? $affiliateOrder->order_id }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                    <span>{{ number_format($affiliateOrder->order_amount) }} Toman</span>
                                    <span>·</span>
                                    <span>{{ $affiliateOrder->source->label() }}</span>
                                    <span>·</span>
                                    <span>{{ (($affiliateOrder->created_at) ? \Illuminate\Support\Carbon::parse($affiliateOrder->created_at)->format('Y/m/d H:i') : '') }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 text-left">
                                @if($affiliateOrder->status->value === 'active')
                                    <div class="text-xs font-black text-emerald-600 dark:text-emerald-300">+{{ number_format($affiliateOrder->commission_amount) }} T</div>
                                @else
                                    <div class="text-[10px] font-black text-rose-500">Refunded</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            No orders have been attributed to your link yet.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Withdrawal --}}
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">Withdrawal request</div>
                    <p class="mt-1 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Minimum withdrawal: {{ number_format($affiliate->min_withdrawal_amount) }} Toman
                    </p>

                    @if($overview['pendingWithdrawalAmount'] > 0)
                        <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-bold leading-5 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                            You have a pending request; submit another after it is resolved.
                        </div>
                    @elseif($canWithdraw)
                        <form action="{{ route('account.affiliate.withdrawals.store') }}" method="POST" class="mt-3 space-y-2.5">
                            @csrf
                            <input type="number" name="amount" min="{{ $affiliate->min_withdrawal_amount }}" max="{{ $overview['availableBalance'] }}" required
                                   placeholder="Amount in toman"
                                   class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-800 outline-none transition focus:border-brand-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <input type="text" name="destination" placeholder="Card number / IBAN (optional)"
                                   class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-800 outline-none transition focus:border-brand-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                            <button type="submit"
                                    class="h-11 w-full rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 text-xs font-extrabold text-white shadow-lg shadow-brand-500/25 transition hover:-translate-y-0.5">
                                Submit withdrawal request
                            </button>
                        </form>
                    @else
                        <div class="mt-3 rounded-2xl border border-dashed border-slate-300 px-3 py-3 text-center text-[11px] font-bold leading-5 text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            Current withdrawable balance {{ number_format($overview['availableBalance']) }} toman;
                            upon reaching the minimum of {{ number_format($affiliate->min_withdrawal_amount) }} toman you can request a withdrawal.
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">Withdrawal history</div>
                    <div class="mt-3 space-y-2">
                        @forelse($overview['withdrawals'] as $withdrawal)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 px-3 py-2.5 dark:border-slate-700">
                                <div>
                                    <div class="text-xs font-black text-slate-800 dark:text-slate-200">{{ number_format($withdrawal->amount) }} Toman</div>
                                    <div class="mt-0.5 text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ (($withdrawal->requested_at) ? \Illuminate\Support\Carbon::parse($withdrawal->requested_at)->format('Y/m/d H:i') : '') }}</div>
                                </div>
                                <div class="text-[10px] font-black
                                    {{ $withdrawal->status->value === 'approved' ? 'text-emerald-600 dark:text-emerald-300' : ($withdrawal->status->value === 'rejected' ? 'text-rose-500' : 'text-amber-600 dark:text-amber-300') }}">
                                    {{ $withdrawal->status->label() }}
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-3 py-4 text-center text-[11px] text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No withdrawal requests yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== How it works ===================== --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="text-sm font-black text-slate-900 dark:text-slate-100">How does the affiliate program work?</div>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/50">
                    <div class="grid h-8 w-8 place-items-center rounded-full bg-brand-500/15 text-xs font-black text-brand-700 dark:text-brand-300">1</div>
                    <div class="mt-2 text-xs font-black text-slate-900 dark:text-slate-100">Share the link or code</div>
                    <p class="mt-1.5 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Post your personal link on social media or give buyers the discount code.
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/50">
                    <div class="grid h-8 w-8 place-items-center rounded-full bg-brand-500/15 text-xs font-black text-brand-700 dark:text-brand-300">2</div>
                    <div class="mt-2 text-xs font-black text-slate-900 dark:text-slate-100">The order is attributed to you</div>
                    <p class="mt-1.5 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Registration through your link within {{ $affiliate->attribution_days }} days and purchases using your code are directly attributed to your account.
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/50">
                    <div class="grid h-8 w-8 place-items-center rounded-full bg-brand-500/15 text-xs font-black text-brand-700 dark:text-brand-300">3</div>
                    <div class="mt-2 text-xs font-black text-slate-900 dark:text-slate-100">Earn commission</div>
                    <p class="mt-1.5 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Commission from every paid order is immediately added to your balance; withdraw after reaching the minimum.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
