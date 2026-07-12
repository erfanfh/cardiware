<section
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
           dark:border-slate-800 dark:bg-slate-900"
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-black text-slate-900 dark:text-white">
                شارژ کیف پول
            </h2>

            <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                مبلغ موردنظر و درگاه پرداخت را انتخاب کنید.
            </p>
        </div>

        <div
            class="inline-flex w-fit items-center rounded-xl bg-brand-50 px-3 py-2
                   text-xs font-bold text-brand-700
                   dark:bg-brand-500/10 dark:text-brand-300"
        >
            پرداخت امن از طریق درگاه بانکی
        </div>
    </div>

    @error('form')
    <div
        class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3
                   text-xs font-semibold leading-6 text-red-700
                   dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
    >
        {{ $message }}
    </div>
    @enderror

    <form
        wire:submit.prevent="submit"
        class="mt-6 space-y-5"
        novalidate
    >
        {{-- مبلغ‌های پیشنهادی --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                انتخاب سریع مبلغ
            </label>

            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach($suggestedAmounts as $suggestedAmount)
                    <button
                        type="button"
                        wire:click="selectAmount({{ $suggestedAmount }})"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="rounded-xl border px-3 py-3 text-xs font-black transition
                               disabled:cursor-not-allowed disabled:opacity-60

                               {{ (int) $amount === $suggestedAmount
                                    ? 'border-brand-500 bg-brand-50 text-brand-700 ring-2 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20'
                                    : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-brand-300 hover:bg-brand-50/50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-brand-700' }}"
                    >
                        {{ number_format($suggestedAmount) }}
                        <span class="font-semibold">تومان</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- مبلغ دلخواه --}}
        <div>
            <label
                for="wallet-top-up-amount"
                class="block text-xs font-bold text-slate-700 dark:text-slate-300"
            >
                مبلغ شارژ
            </label>

            <div class="relative mt-2">
                <input
                    id="wallet-top-up-amount"
                    type="text"
                    inputmode="numeric"
                    autocomplete="off"
                    dir="ltr"
                    wire:model.blur="amount"
                    placeholder="{{ number_format($minimumAmount) }}"
                    class="h-12 w-full rounded-xl border bg-slate-50 px-4 pl-20
                           text-left text-sm font-bold text-slate-900 outline-none transition

                           {{ $errors->has('amount')
                                ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
                                : 'border-slate-200 focus:border-brand-400 focus:bg-white focus:ring-brand-100' }}

                           focus:ring-4
                           dark:border-slate-700 dark:bg-slate-950 dark:text-white
                           dark:focus:bg-slate-950 dark:focus:ring-brand-500/15"
                >

                <span
                    class="pointer-events-none absolute inset-y-0 left-4 flex items-center
                           text-xs font-bold text-slate-400"
                >
                    تومان
                </span>
            </div>

            @error('amount')
            <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
            @enderror

            <p class="mt-2 text-[11px] leading-5 text-slate-400">
                حداقل {{ number_format($minimumAmount) }} تومان و حداکثر
                {{ number_format($maximumAmount) }} تومان
            </p>
        </div>

        {{-- انتخاب درگاه --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                درگاه پرداخت
            </label>

            @if(count($gateways) > 0)
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach($gateways as $gatewayItem)
                        <label
                            wire:key="wallet-gateway-{{ $gatewayItem->value }}"
                            class="relative flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition

                                   {{ $gateway === $gatewayItem->value
                                        ? 'border-brand-500 bg-brand-50/70 ring-2 ring-brand-100 dark:bg-brand-500/10 dark:ring-brand-500/20'
                                        : 'border-slate-200 bg-slate-50 hover:border-brand-300 dark:border-slate-700 dark:bg-slate-950' }}"
                        >
                            <input
                                type="radio"
                                wire:model="gateway"
                                value="{{ $gatewayItem->value }}"
                                class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500"
                            >

                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl
                                       bg-white text-brand-600 shadow-sm
                                       dark:bg-slate-900 dark:text-brand-300"
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
                                        d="M3.75 7.5h16.5M5.25 4.5h13.5A1.5 1.5 0 0 1 20.25 6v12a1.5 1.5 0 0 1-1.5 1.5H5.25A1.5 1.5 0 0 1 3.75 18V6a1.5 1.5 0 0 1 1.5-1.5Z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7.5 15.75h3"
                                    />
                                </svg>
                            </span>

                            <span>
                                <span class="block text-sm font-black text-slate-800 dark:text-slate-200">
                                    {{ $gatewayItem->label() }}
                                </span>

                                <span class="mt-1 block text-[11px] text-slate-400">
                                    پرداخت آنلاین
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <div
                    class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4
                           text-xs font-semibold leading-6 text-amber-700
                           dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                >
                    در حال حاضر هیچ درگاه فعالی برای شارژ کیف پول وجود ندارد.
                </div>
            @endif

            @error('gateway')
            <p class="mt-2 text-xs font-semibold text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
            @enderror
        </div>

        {{-- نکته --}}
        <div
            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3
                   text-[11px] leading-6 text-slate-500
                   dark:border-slate-700 dark:bg-slate-950 dark:text-slate-400"
        >
            مبلغ شارژشده برای خرید از سایت قابل استفاده است. امکان برداشت وجه شارژشده
            مطابق قوانین کیف پول و پس از فعال‌شدن بخش برداشت تعیین می‌شود.
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="submit"
            @disabled(count($gateways) === 0)
            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl
                   bg-brand-600 px-5 text-sm font-black text-white transition
                   hover:bg-brand-700
                   disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="submit">
                انتقال به درگاه پرداخت
            </span>

            <span
                wire:loading.flex
                wire:target="submit"
                class="items-center gap-2"
            >
                <svg
                    class="h-5 w-5 animate-spin"
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

                در حال اتصال به درگاه...
            </span>
        </button>
    </form>
</section>
