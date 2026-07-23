@php
    use App\Domain\Financial\Enums\WalletStatusEnum;

    $wallet ??= auth()->user()?->wallet;

    $totalBalance = (int) ($wallet?->balance ?? 0);
    $heldBalance = (int) ($wallet?->held_balance ?? 0);
    $availableBalance = max(0, $totalBalance - $heldBalance);

    $status = $wallet?->status?->value
        ?? $wallet?->status
        ?? WalletStatusEnum::ACTIVE->value;

    $isBlocked = $status === WalletStatusEnum::BLOCKED->value;
    $hasAvailableBalance = $availableBalance > 0;
    $hasHeldBalance = $heldBalance > 0;
@endphp

<x-front.cart
    class="group relative overflow-hidden !p-4 transition duration-300 hover:border-brand-200 hover:shadow-[0_9px_28px_rgba(249,115,22,0.09)] dark:hover:border-brand-900/60">
    {{-- افکت ظریف پس زمینه --}}
    <div
        class="pointer-events-none absolute -right-12 -top-14
               h-28 w-28 rounded-full bg-orange-100/70 blur-3xl
               dark:bg-orange-500/5"
    ></div>

    <div
        class="pointer-events-none absolute -bottom-16 -left-14
               h-24 w-24 rounded-full bg-amber-100/50 blur-3xl
               dark:bg-amber-500/5"
    ></div>

    <div class="relative">
        {{-- Header --}}
        <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="grid h-10 w-10 shrink-0 place-items-center
                           rounded-2xl border border-orange-200/70
                           bg-orange-50 text-orange-600
                           shadow-sm shadow-orange-500/5
                           dark:border-orange-900/40
                           dark:bg-orange-950/20
                           dark:text-orange-300 dark:shadow-none
                           [&_svg]:w-5 [&_svg]:h-5"
                >
                    @include('icons.wallet-03')
                </div>

                <div class="min-w-0">
                    <h2
                        class="truncate text-sm font-black
                               text-slate-900 dark:text-white"
                    >
                        کیف پول من
                    </h2>

                    <p
                        class="mt-0.5 truncate text-[10px] font-semibold
                               text-slate-500 dark:text-slate-400"
                    >
                        موجودی و تراکنش های کیف پول
                    </p>
                </div>
            </div>

            @if($isBlocked)
                <span
                    class="inline-flex shrink-0 items-center gap-1
                           rounded-full bg-red-50 px-2 py-1
                           text-[9px] font-extrabold text-red-600
                           ring-1 ring-red-100
                           dark:bg-red-950/30 dark:text-red-300
                           dark:ring-red-900/40"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>

                    مسدود
                </span>
            @endif
        </div>

        {{-- Balance and action --}}
        <div
            class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
        >
            <div
                class="min-w-0 flex-1"
                x-data="{
                    availableBalance: {{ (int) $availableBalance }},
                    hasHeldBalance: @js($hasHeldBalance),

                    formatAmount(value) {
                        return new Intl.NumberFormat('en-US').format(
                            Number(value) || 0
                        );
                    }
                }"
                x-on:wallet-balance-updated.window="
                    availableBalance = Number(
                        $event.detail.availableBalance ?? availableBalance
                    );

                    if ($event.detail.hasHeldBalance !== undefined) {
                        hasHeldBalance = Boolean(
                            $event.detail.hasHeldBalance
                        );
                    }
                "
            >
                <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                    موجودی قابل استفاده
                </div>

                <div
                    x-show="availableBalance > 0"
                    x-cloak
                    class="mt-1 flex items-end gap-1"
                >
                    <strong
                        class="truncate text-xl font-black leading-none tracking-tight text-slate-950 dark:text-white"
                        dir="ltr"
                        x-text="formatAmount(availableBalance)"
                    >
                        {{ number_format($availableBalance) }}
                    </strong>

                    <span class="shrink-0 pb-0.5 text-[10px] font-extrabold text-orange-600 dark:text-orange-300">
                        تومان
                    </span>
                </div>

                <div
                    x-show="availableBalance <= 0"
                    x-cloak
                    class="mt-1 text-[0.8rem] font-blacktext-slate-800 dark:text-slate-100"
                >
                    <span x-show="hasHeldBalance">
                        موجودی قابل استفاده ندارید!
                    </span>

                    <span x-show="!hasHeldBalance">
                        موجودی ندارید!
                    </span>
                </div>
            </div>

            <a
                href="{{ route('account.wallet') }}"
                class="inline-flex h-10 shrink-0 items-center
                       justify-center gap-1.5 rounded-xl
                       bg-orange-500 px-3.5
                       text-[10px] font-extrabold text-white
                       shadow-sm shadow-orange-500/20
                       transition duration-200
                       hover:bg-orange-600
                       active:scale-[0.98]
                       dark:bg-orange-600 dark:shadow-none
                       dark:hover:bg-orange-500"
            >
                <span>
                    مشاهده کیف پول
                </span>

                <svg
                    class="h-3.5 w-3.5 transition-transform duration-200
                           group-hover:-translate-x-0.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M19 12H5m6-6-6 6 6 6"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </a>
        </div>

        {{-- Held balance --}}
        @if($hasHeldBalance)
            <div
                class="mt-3 flex items-center justify-between gap-3
                       rounded-xl border border-amber-200/70
                       bg-amber-50/70 px-3 py-2
                       dark:border-amber-900/40
                       dark:bg-amber-950/20"
            >
                <span
                    class="text-[10px] font-bold
                           text-amber-800 dark:text-amber-300"
                >
                    موجودی رزرو شده
                </span>

                <span
                    class="text-[10px] font-extrabold
                           text-amber-700 dark:text-amber-200"
                >
                    {{ number_format($heldBalance) }}
                    تومان
                </span>
            </div>
        @endif
    </div>
</x-front.cart>
