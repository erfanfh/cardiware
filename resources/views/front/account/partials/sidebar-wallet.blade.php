@php
    use App\Domain\Financial\Enums\WalletStatusEnum;

    /*
     * Wallet can be passed from the parent view.
     * When it is not passed, the authenticated user's wallet is resolved.
     */
    $wallet = auth()->user()?->wallet;

    $totalBalance = (int) ($wallet?->balance ?? 0);
    $heldBalance = (int) ($wallet?->held_balance ?? 0);

    /*
     * Active holds must not be presented as spendable balance.
     */
    $availableBalance = max(
        0,
        $totalBalance - $heldBalance,
    );

    $rawStatus = $wallet?->status?->value
        ?? $wallet?->status
        ?? WalletStatusEnum::ACTIVE->value;

    $isBlocked = $rawStatus === WalletStatusEnum::BLOCKED->value;

    $availablePercent = $totalBalance > 0
        ? min(100, max(0, ($availableBalance / $totalBalance) * 100))
        : 0;
@endphp

<section
    class="group relative overflow-hidden rounded-[26px] border border-orange-200/70
           bg-gradient-to-br from-orange-50 via-white to-amber-50/70
           p-4 shadow-sm transition duration-300
           hover:-translate-y-0.5 hover:shadow-lg hover:shadow-orange-500/10
           dark:border-orange-900/40
           dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900
           dark:to-orange-950/30 dark:hover:shadow-orange-950/30
           sm:p-5"
>
    {{-- Decorative background --}}
    <div
        class="pointer-events-none absolute -left-10 -top-12 h-32 w-32
               rounded-full bg-orange-200/40 blur-2xl transition duration-500
               group-hover:scale-110
               dark:bg-orange-600/10"
    ></div>

    <div
        class="pointer-events-none absolute -bottom-16 -right-14 h-36 w-36
               rounded-full bg-amber-200/40 blur-3xl transition duration-500
               group-hover:scale-110
               dark:bg-amber-500/10"
    ></div>

    <div class="relative">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl
                           border border-orange-200 bg-white text-orange-600
                           shadow-sm shadow-orange-500/10
                           dark:border-orange-900/50 dark:bg-slate-800
                           dark:text-orange-300 dark:shadow-none"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M5 7h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 0V5a2 2 0 0 1 2-2h9"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M16 12h5v4h-5a2 2 0 1 1 0-4Z"
                            stroke="currentColor"
                            stroke-width="1.9"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M17.5 14h.01"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2
                        class="truncate text-sm font-black text-slate-900
                               dark:text-white"
                    >
                        کیف پول من
                    </h2>

                    <p
                        class="mt-1 truncate text-[11px] font-semibold
                               text-slate-500 dark:text-slate-400"
                    >
                        مدیریت موجودی و تراکنش‌ها
                    </p>
                </div>
            </div>

            @if($isBlocked)
                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full
                           border border-red-200 bg-red-50 px-2.5 py-1
                           text-[10px] font-extrabold text-red-700
                           dark:border-red-900/50 dark:bg-red-950/30
                           dark:text-red-300"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    مسدود
                </span>
            @else
                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full
                           border border-emerald-200 bg-emerald-50 px-2.5 py-1
                           text-[10px] font-extrabold text-emerald-700
                           dark:border-emerald-900/50 dark:bg-emerald-950/30
                           dark:text-emerald-300"
                >
                    <span
                        class="h-1.5 w-1.5 animate-pulse rounded-full
                               bg-emerald-500"
                    ></span>

                    فعال
                </span>
            @endif
        </div>

        {{-- Balance --}}
        <div class="mt-5">
            <div
                class="text-[11px] font-bold text-slate-500
                       dark:text-slate-400"
            >
                موجودی قابل استفاده
            </div>

            <div class="mt-1 flex flex-wrap items-end gap-1.5">
                <strong
                    class="text-2xl font-black tracking-tight text-slate-950
                           dark:text-white"
                    dir="ltr"
                >
                    {{ number_format($availableBalance) }}
                </strong>

                <span
                    class="pb-1 text-xs font-extrabold text-orange-600
                           dark:text-orange-300"
                >
                    تومان
                </span>
            </div>
        </div>

        {{-- Balance status --}}
        @if($heldBalance > 0)
            <div class="mt-4">
                <div
                    class="mb-2 flex items-center justify-between gap-3
                           text-[10px] font-bold"
                >
                    <span class="text-slate-500 dark:text-slate-400">
                        موجودی آزاد
                    </span>

                    <span class="text-amber-700 dark:text-amber-300">
                        {{ number_format($heldBalance) }}
                        تومان رزرو شده
                    </span>
                </div>

                <div
                    class="h-2 overflow-hidden rounded-full bg-orange-100
                           ring-1 ring-orange-200/50
                           dark:bg-slate-800 dark:ring-slate-700"
                >
                    <div
                        class="h-full rounded-full bg-gradient-to-l
                               from-orange-500 to-amber-400
                               transition-all duration-500"
                        style="width: {{ $availablePercent }}%"
                    ></div>
                </div>
            </div>
        @else
            <div
                class="mt-4 flex items-center gap-2 rounded-2xl
                       border border-orange-100 bg-white/70 px-3 py-2
                       text-[10px] font-bold text-slate-500
                       dark:border-slate-700 dark:bg-slate-800/60
                       dark:text-slate-400"
            >
                <svg
                    class="h-4 w-4 shrink-0 text-emerald-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="m5 12 4 4L19 6"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                تمام موجودی کیف پول قابل استفاده است
            </div>
        @endif

        {{-- Action --}}
        <a
            href="{{ route('account.wallet') }}"
            class="mt-4 flex h-11 w-full items-center justify-between
                   rounded-2xl bg-orange-500 px-4
                   text-xs font-extrabold text-white
                   shadow-md shadow-orange-500/20
                   transition duration-200
                   hover:bg-orange-600 hover:shadow-lg
                   hover:shadow-orange-500/25
                   active:scale-[0.99]
                   dark:bg-orange-600 dark:shadow-none
                   dark:hover:bg-orange-500"
        >
            <span>
                مشاهده و مدیریت کیف پول
            </span>

            <svg
                class="h-4 w-4 transition-transform duration-200
                       group-hover:-translate-x-1"
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
</section>
