@extends('front.account.layout')

@section('page-content')
    <div class="space-y-6">
        {{-- Main wallet balance --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-slate-950 shadow-xl shadow-slate-950/10
           dark:border dark:border-slate-800 dark:shadow-black/20"
        >
            {{-- Decorative backgrounds --}}
            <div
                class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full
               bg-brand-500/20 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-28 -right-20 h-72 w-72 rounded-full
               bg-brand-400/10 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute inset-0
               bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.06),transparent_38%)]"
            ></div>

            <div
                class="relative grid gap-8 p-6 sm:p-7
               lg:grid-cols-[minmax(0,1fr)_minmax(300px,auto)]
               lg:items-center lg:gap-12"
            >
                {{-- Balance information --}}
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl
                           border border-white/10 bg-white/5 text-brand-300 [&_svg]:w-6 [&_svg]:h-6"
                        >
                            @include('icons.wallet-03')
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-400">
                                موجودی قابل استفاده
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                مبلغ در دسترس برای خرید و پرداخت
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-end gap-x-3 gap-y-2">
                        <strong
                            class="text-3xl font-black tracking-tight text-white
                           sm:text-4xl lg:text-5xl"
                            dir="ltr"
                        >
                            {{ number_format($wallet->available_balance) }}
                        </strong>

                        <span class="pb-1 text-sm font-bold text-slate-300 sm:text-base">
                    تومان
                </span>
                    </div>

                    @if ($wallet->held_balance > 0)
                        <div
                            class="mt-6 inline-flex max-w-full items-center gap-2 rounded-xl
                           border border-amber-300/15 bg-amber-300/10 px-3 py-2"
                        >
                            <svg
                                class="h-4 w-4 shrink-0 text-amber-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 6v6l4 2"
                                />

                                <circle cx="12" cy="12" r="8.25"></circle>
                            </svg>

                            <p class="text-xs leading-6 text-amber-100">
                                مبلغ

                                <span class="font-black" dir="ltr">
                            {{ number_format($wallet->held_balance) }}
                        </span>

                                تومان از موجودی شما موقتا رزرو شده است.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Wallet actions --}}
                <div class="w-full lg:w-auto lg:min-w-[320px]">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">

                        {{-- Top-up button --}}
                        <a
                            href="#wallet-topup"
                            class="group flex w-full items-center justify-center gap-2 rounded-2xl
                           bg-brand-500 px-5 py-3.5 text-sm font-black text-white
                           shadow-lg shadow-brand-500/20 transition
                           hover:bg-brand-600 hover:shadow-brand-500/30
                           focus:outline-none focus:ring-4 focus:ring-brand-500/20"
                        >
                            <svg
                                class="h-5 w-5 transition-transform
                               group-hover:-translate-y-0.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>

                            افزایش موجودی
                        </a>

                        {{-- Transfer button --}}
                        <a
                            href="#wallet-transfer"
                            class="group flex w-full items-center justify-center gap-2 rounded-2xl
                           border border-white/10 bg-white/5 px-5 py-3.5
                           text-sm font-black text-white transition
                           hover:border-white/20 hover:bg-white/10
                           focus:outline-none focus:ring-4 focus:ring-white/10"
                        >
                            <svg
                                class="h-5 w-5 text-slate-300 transition-transform
                               group-hover:-translate-x-0.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7.5 7.5h10.75m0 0-3.25-3.25M18.25 7.5 15 10.75"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.5 16.5H5.75m0 0L9 19.75M5.75 16.5 9 13.25"
                                />
                            </svg>

                            انتقال پول
                        </a>
                    </div>

                    <p class="mt-3 text-center text-xs leading-6 text-slate-500">
                        افزایش موجودی یا انتقال اعتبار به کاربران دیگر
                    </p>
                </div>
            </div>
        </section>

        {{-- Held balance information --}}
        @if ($wallet->held_balance > 0)
            <section
                class="flex items-start gap-3 rounded-2xl border border-amber-200
               bg-amber-50 p-4
               dark:border-amber-500/20 dark:bg-amber-500/10"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                   bg-amber-100 text-amber-600
                   dark:bg-amber-500/15 dark:text-amber-300"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m9-1.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 16.5h.008v.008H12V16.5Z"
                        />
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-black text-amber-900 dark:text-amber-200">
                        بخشی از موجودی شما رزرو شده است
                    </p>

                    <p class="mt-1 text-xs leading-6 text-amber-700 dark:text-amber-300/80">
                        موجودی رزرو شده مربوط به پرداخت یا سفارش در حال پردازش است و پس از
                        تعیین وضعیت نهایی، آزاد یا مصرف خواهد شد.
                    </p>
                </div>
            </section>
        @endif

        <livewire:front.account.wallet.top-up-form/>

        <livewire:front.account.wallet.transactions-list/>
    </div>
@endsection
