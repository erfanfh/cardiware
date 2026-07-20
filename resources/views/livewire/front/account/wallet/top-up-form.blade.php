<x-front.cart
    id="wallet-topup"
    class="scroll-mt-36 !p-4 sm:!p-5"
>
    <div
        x-data="{
            amount: @js($amount),

            toEnglishDigits(value) {
                return String(value)
                    .replace(/[۰-۹]/g, digit => '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))
                    .replace(/[٠-٩]/g, digit => '٠١٢٣٤٥٦٧٨٩'.indexOf(digit));
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

                return rawValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },

            selectAmount(value) {
                this.amount = this.formatAmount(value);
            },

            isSelectedAmount(value) {
                return Number(this.rawAmount(this.amount)) === Number(value);
            },

            submit() {
                this.$wire.submit(this.amount);
            }
        }"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-black text-slate-900 sm:text-base dark:text-white">
                    افزایش موجودی
                </h2>

                <p class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                    مبلغ مورد نظر را وارد کرده و پرداخت را انجام دهید.
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
                        d="M12 5v14M5 12h14"
                    />
                </svg>
            </div>
        </div>

        {{-- General error --}}
        @error('form')
        <div
            class="mt-4 flex items-start gap-2 rounded-xl border border-red-200
                       bg-red-50 px-3 py-2.5 text-xs font-semibold leading-6 text-red-700
                       dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
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

        @unless($gatewayAvailable)
            <div
                class="mt-4 rounded-xl border border-amber-200 bg-amber-50
                       px-3 py-2.5 text-xs font-semibold leading-6 text-amber-700
                       dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
            >
                درگاه پیشفرض پرداخت تنظیم نشده یا برای پرداخت آنلاین معتبر نیست.
            </div>
        @endunless

        <form
            x-on:submit.prevent="submit"
            class="mt-4"
            novalidate
        >
            {{-- Quick amounts --}}
            <div>
                <div class="flex items-center justify-between gap-3">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        مبلغ شارژ
                    </label>

                    <span class="text-[10px] text-slate-400">
                        حداقل {{ number_format($minimumAmount) }} تومان
                    </span>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($suggestedAmounts as $suggestedAmount)
                        <button
                            type="button"
                            x-on:click="selectAmount({{ $suggestedAmount }})"
                            x-bind:class="
                                isSelectedAmount({{ $suggestedAmount }})
                                    ? 'border-brand-500 bg-brand-50 text-brand-700 ring-2 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20'
                                    : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-brand-300 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-brand-700 dark:hover:text-brand-300'
                            "
                            class="cursor-pointer rounded-xl border px-2 py-2.5 text-xs font-black transition"
                        >
                            {{ number_format($suggestedAmount) }}

                            <span class="text-[10px] font-semibold">
                                تومان
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Amount input and submit --}}
            <div
                class="mt-3 grid gap-2
                       sm:grid-cols-[minmax(0,1fr)_180px]"
            >
                <div>
                    <div class="relative">
                        <input
                            id="wallet-top-up-amount"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            dir="ltr"
                            x-bind:value="amount"
                            x-on:input="
                                amount = formatAmount($event.target.value);
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
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @disabled(!$gatewayAvailable)
                    class="cursor-pointer flex h-12 w-full items-center justify-center gap-2
                           rounded-xl bg-brand-600 px-4 text-sm font-black text-white
                           shadow-sm transition hover:bg-brand-700
                           disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span
                        wire:loading.remove
                        wire:target="submit"
                        class="flex items-center gap-2 [&_svg]:w-5 [&_svg]:h-5"
                    >
                        پرداخت

                        @include('icons.credit-card')
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

                        در حال اتصال
                    </span>
                </button>
            </div>

            @error('amount')
            <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
            @enderror

            <div
                class="mt-3 flex flex-col gap-1 text-[10px] leading-5 text-slate-400
                       sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    پرداخت از طریق درگاه امن سیستم انجام می شود.
                </p>

                <p>
                    حداکثر مبلغ:
                    <span class="font-bold text-slate-500 dark:text-slate-300">
                        {{ number_format($maximumAmount) }} تومان
                    </span>
                </p>
            </div>
        </form>
    </div>
</x-front.cart>
