<div
    x-cloak
    x-show="checkoutModalOpen"
    x-transition.opacity.duration.150ms
    @keydown.escape.window="checkoutModalOpen = false"
    class="fixed inset-0 z-[90] flex items-end justify-center px-0 py-0 sm:items-center sm:px-4 sm:py-6"
    style="display: none;"
>
    <div
        class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm dark:bg-black/80"
        @click="checkoutModalOpen = false"
    ></div>

    <div
        x-show="checkoutModalOpen"
        x-transition.scale.origin.center.duration.150ms
        class="relative max-h-[92vh] w-full overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl
    dark:border-slate-700 dark:bg-slate-900 sm:max-w-2xl sm:rounded-3xl"
    >
        <div
            class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-700">

            <div class="min-w-0">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                    تکمیل پرداخت
                </h2>

                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                    روش پرداخت را انتخاب کنید و سفارش خود را تکمیل کنید.
                </p>
            </div>

            <button
                type="button"
                @click="checkoutModalOpen = false"
                class="grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800
        dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                aria-label="بستن"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

        </div>

        <form
            x-on:submit.prevent="submitCheckout"
            class="max-h-[calc(92vh-73px)] overflow-y-auto p-4 sm:p-6"
        >
            @if($checkoutError)
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 sm:mb-5 sm:rounded-3xl
        dark:border-red-900/40 dark:bg-red-950/30">

                    <div class="flex items-start gap-3">

            <span
                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-red-600 ring-1 ring-red-100
                dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/40">

                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

            </span>

                        <div class="min-w-0 flex-1">

                            <div class="text-sm font-extrabold text-red-900 dark:text-red-200">
                                پرداخت قابل انجام نیست
                            </div>

                            <p class="mt-1 text-xs leading-6 text-red-700 dark:text-red-300">
                                {{ $checkoutError }}
                            </p>

                            @if($cartNeedsRefresh)
                                <button
                                    type="button"
                                    wire:click="refreshCart"
                                    wire:loading.attr="disabled"
                                    wire:target="refreshCart"
                                    class="mt-3 inline-flex h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 text-xs font-extrabold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto
                        dark:bg-red-700 dark:hover:bg-red-800"
                                >
                        <span wire:loading.remove wire:target="refreshCart">
                            به‌روزرسانی سبد خرید
                        </span>

                                    <span wire:loading wire:target="refreshCart">
                            در حال به‌روزرسانی...
                        </span>
                                </button>
                            @endif

                        </div>

                    </div>

                </div>
            @endif

            @if($checkoutNotice)
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:mb-5 sm:rounded-3xl
        dark:border-emerald-900/40 dark:bg-emerald-950/30">

                    <div class="flex items-start gap-3">

            <span
                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100
                dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900/40">

                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>

            </span>

                        <div class="min-w-0">

                            <div class="text-sm font-extrabold text-emerald-900 dark:text-emerald-200">
                                سبد خرید به‌روزرسانی شد
                            </div>

                            <p class="mt-1 text-xs leading-6 text-emerald-700 dark:text-emerald-300">
                                {{ $checkoutNotice }}
                            </p>

                        </div>

                    </div>

                </div>
            @endif

            <div>
                <div class="mb-3 text-xs font-extrabold text-slate-700 dark:text-slate-300">
                    روش پرداخت را انتخاب کنید
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <label
                        class="cursor-pointer rounded-2xl border p-4 transition sm:rounded-3xl
                            dark:border-slate-800 dark:bg-slate-900/40 dark:hover:bg-slate-900/60"
                        :class="paymentMethod === 'gateway'
                            ? 'border-brand-200 bg-brand-50 ring-4 ring-brand-100 dark:border-brand-700 dark:bg-brand-950/30 dark:ring-brand-900/40'
                            : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:bg-slate-900/50'"
                    >

                        <input
                            type="radio"
                            value="gateway"
                            x-model="paymentMethod"
                            x-bind:disabled="checkoutSubmitting"
                            class="sr-only"
                        >

                        <div class="flex items-start gap-3">

                            <div
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl
                                    dark:bg-slate-800 dark:text-slate-300"
                                :class="paymentMethod === 'gateway'
                                    ? 'bg-brand-500 text-white dark:bg-brand-600'
                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M3 10h18M7 15h1m4 0h5M6 19h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0">

                                <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                    درگاه پرداخت
                                </div>

                                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                    پرداخت آنلاین از طریق درگاه بانکی.
                                </p>

                            </div>

                        </div>

                    </label>

                    @php
                        $walletMode = $this->walletPaymentMode;
                        $walletCanBeSelected = $this->canUseWalletPayment;
                    @endphp

                    <label
                        class="rounded-2xl border p-4 transition sm:rounded-3xl
                       {{ $walletCanBeSelected
                            ? 'cursor-pointer'
                            : 'cursor-not-allowed opacity-65' }}"
                        x-bind:class="[
                            paymentMethod === 'wallet'
                                ? 'border-brand-200 bg-brand-50 ring-4 ring-brand-100 dark:border-brand-700 dark:bg-brand-950/30 dark:ring-brand-900/40'
                                : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900/30 dark:hover:bg-slate-900/50',

                            checkoutSubmitting
                                ? 'pointer-events-none opacity-70'
                                : ''
                        ]"
                    >
                        <input
                            type="radio"
                            value="wallet"
                            x-model="paymentMethod"
                            x-bind:disabled="checkoutSubmitting"
                            class="sr-only"
                            @disabled(!$walletCanBeSelected)
                        >

                        <div class="flex items-start gap-3">
                            <div
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl"
                                :class="paymentMethod === 'wallet'
                                    ? 'bg-brand-500 text-white dark:bg-brand-600'
                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M5 7h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 0V5a2 2 0 0 1 2-2h9"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />

                                    <path
                                        d="M17 13h.01"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                        @if($walletMode === 'mixed')
                                            کیف پول + درگاه
                                        @else
                                            کیف پول
                                        @endif
                                    </div>

                                    <span
                                        class="rounded-xl border border-slate-200 bg-white px-2 py-1
                                           text-[10px] font-extrabold text-slate-500
                                           dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        موجودی قابل استفاده:
                                        {{ $this->money($this->walletAvailableBalance) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </label>

                </div>

                @if($this->canUseWalletPayment)
                    <div
                        x-cloak
                        x-show="paymentMethod === 'wallet'"
                        x-transition.opacity.duration.150ms
                        class="mt-3 rounded-2xl border border-brand-100 bg-brand-50/60 p-4 dark:border-brand-900/40 dark:bg-brand-950/20"
                    >
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-semibold text-slate-600 dark:text-slate-400">
                                    سهم کیف پول
                                </span>

                                <strong class="font-extrabold text-slate-900 dark:text-slate-100">
                                    {{ $this->money($this->walletContribution) }}
                                </strong>
                            </div>

                            @if($this->walletShortage > 0)
                                <div class="flex items-center justify-between gap-4">
                                    <span class="font-semibold text-slate-600 dark:text-slate-400">
                                        مبلغ پرداخت در درگاه
                                    </span>

                                    <strong class="font-extrabold text-brand-700 dark:text-brand-300">
                                        {{ $this->money($this->walletShortage) }}
                                    </strong>
                                </div>
                            @endif

                            <div
                                class="flex items-center justify-between gap-4 border-t border-brand-100 pt-2 dark:border-brand-900/40"
                            >
                                <span class="font-extrabold text-slate-700 dark:text-slate-300">
                                    مبلغ کل سفارش
                                </span>

                                <strong class="font-black text-slate-900 dark:text-white">
                                    {{ $this->money($this->cartTotal) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:rounded-3xl
    dark:border-slate-800 dark:bg-slate-900/30">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div class="min-w-0">

            <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">
                مبلغ قابل پرداخت
            </span>

                        <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                            قبل از پرداخت، سبد خرید با آخرین تغییرات محصولات بررسی می‌شود.
                        </p>

                    </div>

                    <div class="flex flex-col items-stretch gap-2 sm:items-end">

            <span
                class="text-lg font-black text-slate-900 transition dark:text-slate-100"
                wire:loading.class="animate-pulse"
                wire:target="refreshCart, checkout"
            >
                {{ $this->money($this->cartTotal) }}
            </span>

                        <button
                            type="button"
                            wire:click="refreshCart"
                            wire:loading.attr="disabled"
                            wire:target="refreshCart"
                            class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60
                dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                        >
                <span wire:loading.remove wire:target="refreshCart">
                    به‌روزرسانی سبد
                </span>

                            <span wire:loading wire:target="refreshCart">
                    در حال بررسی...
                </span>
                        </button>

                    </div>

                </div>

            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:flex sm:flex-row-reverse sm:justify-start">

                <button
                    type="submit"
                    x-bind:disabled="checkoutSubmitting"
                    wire:loading.attr="disabled"
                    wire:target="checkout"
                    class="inline-flex h-12 cursor-pointer items-center justify-center gap-2
                       rounded-2xl bg-brand-500 px-6 text-sm font-extrabold text-white
                       shadow-md shadow-brand-500/20 transition
                       hover:bg-brand-600 active:bg-brand-700
                       disabled:cursor-not-allowed disabled:opacity-60
                       dark:bg-brand-600 dark:hover:bg-brand-700 dark:shadow-none"
                >
                    <span
                        x-show="!checkoutSubmitting"
                        class="flex items-center gap-2"
                    >
                        <span x-show="paymentMethod === 'gateway'">
                            پرداخت با درگاه
                        </span>

                        <span
                            x-cloak
                            x-show="paymentMethod === 'wallet'"
                        >
                            @if($this->walletPaymentMode === 'full')
                                پرداخت با کیف پول
                            @elseif($this->walletPaymentMode === 'mixed')
                                تکمیل موجودی و پرداخت
                            @else
                                ادامه پرداخت
                            @endif
                        </span>
                    </span>

                    <span
                        x-cloak
                        x-show="checkoutSubmitting"
                        class="flex items-center gap-2"
                    >
                        <span>
                            در حال پردازش...
                        </span>

                        <span
                            class="h-4 w-4 animate-spin rounded-full
                                   border-2 border-white/40 border-t-white"
                        ></span>
                    </span>
                </button>

                <button
                    type="button"
                    @click="checkoutModalOpen = false"
                    class="inline-flex h-12 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50
        dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    انصراف
                </button>

            </div>
        </form>
    </div>
</div>
