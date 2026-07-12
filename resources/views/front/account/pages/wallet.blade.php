@extends('front.account.layout')

@section('page-content')
    <div class="space-y-6">

        {{-- Page header --}}
        <section
            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                   dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20"
        >
            <div
                class="absolute -left-12 -top-12 h-32 w-32 rounded-full bg-brand-100/70
                       dark:bg-brand-500/10"
            ></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-lg font-black text-slate-900 dark:text-white">
                        کیف پول
                    </h1>

                    <p class="mt-1 text-sm leading-7 text-slate-500 dark:text-slate-400">
                        موجودی، شارژها و تراکنش‌های کیف پول خود را از این بخش مدیریت کنید.
                    </p>
                </div>

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl
                           bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300"
                >
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3.75 7.5A2.25 2.25 0 0 1 6 5.25h11.25A2.25 2.25 0 0 1 19.5 7.5v9A2.25 2.25 0 0 1 17.25 18.75H6A2.25 2.25 0 0 1 3.75 16.5v-9Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 10.5h4.5v3H15a1.5 1.5 0 0 1 0-3Z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 5.25V4.5A1.5 1.5 0 0 1 7.5 3h8.25"
                        />
                    </svg>
                </div>
            </div>
        </section>

        {{-- Main balance --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-lg
                   dark:border dark:border-slate-800"
        >
            <div
                class="absolute -left-16 -top-16 h-44 w-44 rounded-full bg-brand-500/20 blur-2xl"
            ></div>

            <div
                class="absolute -bottom-20 -right-16 h-52 w-52 rounded-full bg-brand-400/10 blur-3xl"
            ></div>

            <div class="relative">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            موجودی قابل استفاده
                        </p>

                        <div class="mt-3 flex items-end gap-2">
                            <strong
                                class="text-3xl font-black tracking-tight sm:text-4xl"
                                dir="ltr"
                            >
                                {{ number_format($wallet->available_balance) }}
                            </strong>

                            <span class="pb-1 text-sm font-bold text-slate-300">
                                تومان
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl
                               border border-white/10 bg-white/5"
                    >
                        <svg
                            class="h-7 w-7 text-brand-300"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v12m3-9.75c-.77-.79-1.91-1.25-3-1.25-1.66 0-3 1.01-3 2.25s1.34 2.25 3 2.25 3 1.01 3 2.25S13.66 16 12 16c-1.09 0-2.23-.46-3-1.25"
                            />
                        </svg>
                    </div>
                </div>

                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="text-xs leading-6 text-slate-400">
                        موجودی قابل استفاده، مبلغی است که در حال حاضر برای خرید یا عملیات مالی
                        در دسترس شما قرار دارد.
                    </p>
                </div>
            </div>
        </section>

        {{-- Wallet statistics --}}
        <section class="grid gap-4 sm:grid-cols-3">

            {{-- Total balance --}}
            <article
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                       dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            موجودی کل
                        </p>

                        <p
                            class="mt-2 text-lg font-black text-slate-900 dark:text-white"
                            dir="ltr"
                        >
                            {{ number_format($wallet->balance) }}
                            <span class="text-xs font-bold text-slate-400">تومان</span>
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                               bg-emerald-50 text-emerald-600
                               dark:bg-emerald-500/10 dark:text-emerald-300"
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
                                d="M12 4.5v15m5.25-7.5H6.75"
                            />
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Withdrawable balance --}}
            <article
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                       dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            قابل برداشت
                        </p>

                        <p
                            class="mt-2 text-lg font-black text-slate-900 dark:text-white"
                            dir="ltr"
                        >
                            {{ number_format($wallet->available_withdrawable_balance) }}

                            <span class="text-xs font-bold text-slate-400">
                                تومان
                            </span>
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                               bg-blue-50 text-blue-600
                               dark:bg-blue-500/10 dark:text-blue-300"
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
                                d="M12 3v14.25m0 0 4.5-4.5M12 17.25l-4.5-4.5M5.25 21h13.5"
                            />
                        </svg>
                    </div>
                </div>
            </article>

            {{-- Held balance --}}
            <article
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                       dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            موجودی رزروشده
                        </p>

                        <p
                            class="mt-2 text-lg font-black text-slate-900 dark:text-white"
                            dir="ltr"
                        >
                            {{ number_format($wallet->held_balance) }}

                            <span class="text-xs font-bold text-slate-400">
                                تومان
                            </span>
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                               bg-amber-50 text-amber-600
                               dark:bg-amber-500/10 dark:text-amber-300"
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
                                d="M12 6v6l4 2"
                            />

                            <circle cx="12" cy="12" r="8.25"></circle>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        {{-- Placeholder for next step --}}
        <section
            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-6
                   dark:border-slate-700 dark:bg-slate-900/50"
        >
            <div class="text-center">
                <h2 class="text-sm font-black text-slate-800 dark:text-slate-200">
                    شارژ کیف پول
                </h2>

                <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                    در مرحله بعد فرم شارژ کیف پول و انتخاب درگاه پرداخت در این بخش قرار می‌گیرد.
                </p>
            </div>
        </section>
    </div>
@endsection
