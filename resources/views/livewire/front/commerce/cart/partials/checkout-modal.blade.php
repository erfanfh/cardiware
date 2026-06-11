<div
    x-cloak
    x-show="checkoutModalOpen"
    x-transition.opacity.duration.150ms
    @keydown.escape.window="checkoutModalOpen = false"
    class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6"
    style="display: none;"
>
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="checkoutModalOpen = false"></div>

    <div
        x-show="checkoutModalOpen"
        x-transition.scale.origin.center.duration.150ms
        class="relative w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
    >
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">تکمیل پرداخت</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                    روش پرداخت را انتخاب کنید و سفارش خود را تکمیل کنید.
                </p>
            </div>

            <button
                type="button"
                @click="checkoutModalOpen = false"
                class="grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
                aria-label="بستن"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form wire:submit.prevent="checkout" class="p-5 sm:p-6">
            @if($checkoutError)
                <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-start gap-3">
                        <span
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-red-600 ring-1 ring-red-100">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-extrabold text-red-900">
                                پرداخت قابل انجام نیست
                            </div>

                            <p class="mt-1 text-xs leading-6 text-red-700">
                                {{ $checkoutError }}
                            </p>

                            @if($cartNeedsRefresh)
                                <button
                                    type="button"
                                    wire:click="refreshCart"
                                    wire:loading.attr="disabled"
                                    wire:target="refreshCart"
                                    class="mt-3 inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-red-600 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
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
                <div class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-start gap-3">
                        <span
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </span>

                        <div>
                            <div class="text-sm font-extrabold text-emerald-900">
                                سبد خرید به‌روزرسانی شد
                            </div>

                            <p class="mt-1 text-xs leading-6 text-emerald-700">
                                {{ $checkoutNotice }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <div class="mb-3 text-xs font-extrabold text-slate-700">روش پرداخت را انتخاب کنید</div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label
                        class="cursor-pointer rounded-3xl border p-4 transition"
                        :class="$wire.paymentMethod === 'gateway' ? 'border-brand-200 bg-brand-50 ring-4 ring-brand-100' : 'border-slate-200 bg-white hover:bg-slate-50'"
                    >
                        <input
                            type="radio"
                            value="gateway"
                            wire:model.live="paymentMethod"
                            class="sr-only"
                        >

                        <div class="flex items-start gap-3">
                            <div
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl"
                                :class="$wire.paymentMethod === 'gateway' ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-600'"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M3 10h18M7 15h1m4 0h5M6 19h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <div>
                                <div class="text-sm font-extrabold text-slate-900">درگاه پرداخت</div>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    پرداخت آنلاین از طریق درگاه بانکی.
                                </p>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-not-allowed rounded-3xl border border-slate-200 bg-slate-50 p-4 opacity-60">
                        <input
                            type="radio"
                            value="wallet"
                            disabled
                            class="sr-only"
                        >

                        <div class="flex items-start gap-3">
                            <div
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M5 7h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 0V5a2 2 0 0 1 2-2h9"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M17 13h.01" stroke="currentColor" stroke-width="3"
                                          stroke-linecap="round"/>
                                </svg>
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-sm font-extrabold text-slate-700">کیف پول</div>
                                    <span
                                        class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[10px] font-extrabold text-slate-500">
                                        موجودی: {{ $this->money($this->walletBalance) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    پرداخت با کیف پول فعلا غیرفعال است.
                                </p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-sm font-extrabold text-slate-700">مبلغ قابل پرداخت</span>

                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            قبل از پرداخت، سبد خرید با آخرین تغییرات محصولات بررسی می‌شود.
                        </p>
                    </div>

                    <div class="flex flex-col items-stretch gap-2 sm:items-end">
                        <span
                            class="text-lg font-black text-slate-900 transition"
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
                            class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
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

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    @click="checkoutModalOpen = false"
                    class="inline-flex cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                >
                    انصراف
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="checkout"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 py-3 text-sm font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 active:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="checkout">
                        پرداخت با درگاه
                    </span>

                    <span wire:loading wire:target="checkout">
                        در حال پردازش...
                    </span>

                    <span
                        wire:loading
                        wire:target="checkout"
                        class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                    ></span>
                </button>
            </div>
        </form>
    </div>
</div>
