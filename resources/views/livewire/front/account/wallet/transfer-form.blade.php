<x-front.cart
    id="wallet-transfer"
    class="scroll-mt-36 !p-4 sm:!p-5"
>
    <div
        x-data="{
            recipientMobile: @js($recipientMobile),
            amount: @js($amount),
            submitting: false,

            toEnglishDigits(value) {
                return String(value)
                    .replace(
                        /[۰-۹]/g,
                        digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)
                    )
                    .replace(
                        /[٠-٩]/g,
                        digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit)
                    );
            },

            sanitizeMobile(value) {
                return this.toEnglishDigits(value)
                    .replace(/[^\d]/g, '')
                    .slice(0, 14);
            },

            rawAmount(value) {
                return this.toEnglishDigits(value)
                    .replace(/[^\d]/g, '')
                    .replace(/^0+(?=\d)/, '');
            },

            formatAmount(value) {
                const rawValue = this.rawAmount(value);

                if (!rawValue) {
                    return '';
                }

                return rawValue.replace(
                    /\B(?=(\d{3})+(?!\d))/g,
                    ','
                );
            },

            selectAmount(value) {
                this.amount = this.formatAmount(value);
            },

            isSelectedAmount(value) {
                return Number(this.rawAmount(this.amount))
                    === Number(value);
            },

            async submitTransfer() {
                if (this.submitting) {
                    return;
                }

                this.submitting = true;

                try {
                    await this.$wire.submit(
                        this.recipientMobile,
                        this.amount
                    );
                } finally {
                    this.submitting = false;
                }
            }
        }"
        x-on:wallet-transfer-form-reset.window="
            recipientMobile = '';
            amount = '';
        "
    >
        {{-- Header --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-black text-slate-900 sm:text-base dark:text-white">
                    انتقال موجودی
                </h2>

                <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                    موجودی قابل برداشت خود را به کیف پول کاربر دیگری انتقال دهید.
                </p>
            </div>

            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                       bg-brand-50 text-brand-600
                       dark:bg-brand-500/10 dark:text-brand-300"
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
                        d="M7.5 7.5h10.75m0 0-3.25-3.25M18.25 7.5 15 10.75"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16.5 16.5H5.75m0 0L9 19.75M5.75 16.5 9 13.25"
                    />
                </svg>
            </div>
        </div>

        {{-- Available balance --}}
        <div
            class="mt-4 flex flex-col gap-2 rounded-xl border border-slate-200
                   bg-slate-50 px-3 py-2.5
                   sm:flex-row sm:items-center sm:justify-between
                   dark:border-slate-700 dark:bg-slate-950"
        >
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                موجودی قابل انتقال
            </span>

            <strong
                class="text-xs font-black text-slate-800 dark:text-slate-200"
            >
                {{ number_format($availableWithdrawableBalance) }}

                <span class="font-semibold text-slate-400">
                    تومان
                </span>
            </strong>
        </div>

        {{-- Success message --}}
        @if($successMessage)
            <div
                class="mt-4 flex items-start gap-2 rounded-xl border border-emerald-200
                       bg-emerald-50 px-3 py-2.5 text-xs font-semibold
                       leading-6 text-emerald-700
                       dark:border-emerald-900/60 dark:bg-emerald-950/30
                       dark:text-emerald-300"
            >
                <svg
                    class="mt-0.5 h-4 w-4 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m5 12 4 4L19 6"
                    />
                </svg>

                <span>{{ $successMessage }}</span>
            </div>
        @endif

        {{-- General error --}}
        @error('form')
        <div
            class="mt-4 flex items-start gap-2 rounded-xl border border-red-200
                       bg-red-50 px-3 py-2.5 text-xs font-semibold
                       leading-6 text-red-700
                       dark:border-red-900/60 dark:bg-red-950/30
                       dark:text-red-300"
        >
            <svg
                class="mt-0.5 h-4 w-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <circle cx="12" cy="12" r="8.5"></circle>

                <path
                    stroke-linecap="round"
                    d="M12 7.75v5M12 16.25h.01"
                />
            </svg>

            <span>{{ $message }}</span>
        </div>
        @enderror

        @unless($walletAvailable)
            <div
                class="mt-4 rounded-xl border border-amber-200 bg-amber-50
                       px-3 py-2.5 text-xs font-semibold leading-6 text-amber-700
                       dark:border-amber-900/60 dark:bg-amber-950/30
                       dark:text-amber-300"
            >
                کیف پول شما برای انتقال موجودی فعال نیست.
            </div>
        @endunless

        <form
            x-on:submit.prevent="submitTransfer"
            class="mt-4"
            novalidate
        >
            {{-- Inputs --}}
            <div class="grid gap-3 md:grid-cols-2">
                {{-- Recipient mobile --}}
                <div>
                    <label
                        for="wallet-transfer-mobile"
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        شماره موبایل مقصد
                    </label>

                    <div class="relative mt-2">
                        <input
                            id="wallet-transfer-mobile"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="off"
                            dir="ltr"
                            x-bind:value="recipientMobile"
                            x-on:input="
                                recipientMobile = sanitizeMobile(
                                    $event.target.value
                                );

                                $event.target.value = recipientMobile;
                            "
                            placeholder="09123456789"
                            class="h-12 w-full rounded-xl border bg-slate-50
                                   px-4 pl-11 text-left text-sm font-bold
                                   text-slate-900 outline-none transition

                                   {{ $errors->has('recipientMobile')
                                        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                        : 'border-slate-200 focus:border-brand-400 focus:bg-white focus:ring-brand-100' }}

                                   focus:ring-4
                                   dark:border-slate-700 dark:bg-slate-950 dark:text-white
                                   dark:focus:bg-slate-950 dark:focus:ring-brand-500/15"
                        >

                        <span
                            class="pointer-events-none absolute inset-y-0 left-4
                                   flex items-center text-slate-400"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.25 4.5h7.5A1.5 1.5 0 0 1 17.25 6v12a1.5 1.5 0 0 1-1.5 1.5h-7.5A1.5 1.5 0 0 1 6.75 18V6a1.5 1.5 0 0 1 1.5-1.5Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M10.5 16.5h3"
                                />
                            </svg>
                        </span>
                    </div>

                    @error('recipientMobile')
                    <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-400">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Amount --}}
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label
                            for="wallet-transfer-amount"
                            class="text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            مبلغ انتقال
                        </label>

                        <span class="text-[10px] text-slate-400">
                            حداقل {{ number_format($minimumAmount) }} تومان
                        </span>
                    </div>

                    <div class="relative mt-2">
                        <input
                            id="wallet-transfer-amount"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            dir="ltr"
                            x-bind:value="amount"
                            x-on:input="
                                amount = formatAmount(
                                    $event.target.value
                                );

                                $event.target.value = amount;
                            "
                            class="h-12 w-full rounded-xl border bg-slate-50
                                   px-4 pl-16 text-left text-sm font-black
                                   text-slate-900 outline-none transition

                                   {{ $errors->has('amount')
                                        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                        : 'border-slate-200 focus:border-brand-400 focus:bg-white focus:ring-brand-100' }}

                                   focus:ring-4
                                   dark:border-slate-700 dark:bg-slate-950 dark:text-white
                                   dark:focus:bg-slate-950 dark:focus:ring-brand-500/15"
                        >

                        <span
                            class="pointer-events-none absolute inset-y-0 left-4
                                   flex items-center text-[11px] font-bold text-slate-400"
                        >
                            تومان
                        </span>
                    </div>

                    @error('amount')
                    <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-400">
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            {{-- Suggested amounts --}}
            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach($suggestedAmounts as $suggestedAmount)
                    <button
                        type="button"
                        x-on:click="selectAmount({{ $suggestedAmount }})"
                        x-bind:disabled="submitting"
                        x-bind:class="
                            isSelectedAmount({{ $suggestedAmount }})
                                ? 'border-brand-500 bg-brand-50 text-brand-700 ring-2 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20'
                                : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-brand-700 dark:hover:text-brand-300'
                        "
                        class="cursor-pointer rounded-xl border px-2 py-2.5
                               text-xs font-black transition
                               disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ number_format($suggestedAmount) }}

                        <span class="text-[10px] font-semibold">
                            تومان
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Footer --}}
            <div
                class="mt-4 flex flex-col gap-3 border-t border-slate-200 pt-4
                       sm:flex-row sm:items-center sm:justify-between
                       dark:border-slate-800"
            >
                <div>
                    <p class="text-[11px] font-semibold leading-5 text-slate-500 dark:text-slate-400">
                        انتقال فقط از موجودی قابل برداشت انجام می‌شود.
                    </p>

                    <p class="mt-0.5 text-[10px] leading-5 text-slate-400">
                        شماره موبایل مقصد را با دقت بررسی کنید؛ انتقال پس از انجام قابل بازگشت نیست.
                    </p>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="submitting"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @disabled(
                        !$walletAvailable
                        || $availableWithdrawableBalance <= 0
                    )
                    class="flex h-12 w-full cursor-pointer items-center justify-center
                           gap-2 rounded-xl bg-brand-600 px-6 text-sm font-black
                           text-white shadow-sm transition hover:bg-brand-700
                           disabled:cursor-not-allowed disabled:opacity-60
                           sm:w-auto sm:min-w-[190px]"
                >
                    <span
                        wire:loading.remove
                        wire:target="submit"
                        class="flex items-center gap-2"
                    >
                        انتقال موجودی

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 12h14M13 6l6 6-6 6"
                            />
                        </svg>
                    </span>

                    <span
                        wire:loading.flex
                        wire:target="submit"
                        class="items-center gap-2"
                    >
                        <svg
                            class="h-4 w-4 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                            ></circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"
                            ></path>
                        </svg>

                        در حال انتقال
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-front.cart>
