@php
    use App\Domain\Commerce\Models\Order;
    use App\Domain\Financial\Models\WalletTopUp;
@endphp

<section
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm
           dark:border-slate-800 dark:bg-slate-900"
>
    {{-- Header and filters --}}
    <div
        class="border-b border-slate-200 p-5
               dark:border-slate-800"
    >
        <div
            class="flex flex-col gap-4
                   lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">
                    تراکنش‌های کیف پول
                </h2>

                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                    تاریخچه افزایش و کاهش موجودی کیف پول شما
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto">
                {{-- Direction filter --}}
                <div>
                    <label
                        for="wallet-transaction-direction"
                        class="sr-only"
                    >
                        جهت تراکنش
                    </label>

                    <select
                        id="wallet-transaction-direction"
                        wire:model.live="direction"
                        class="h-10 w-full rounded-xl border border-slate-200
                               bg-slate-50 px-3 text-xs font-bold text-slate-700
                               outline-none transition
                               focus:border-brand-400 focus:bg-white
                               focus:ring-4 focus:ring-brand-100
                               dark:border-slate-700 dark:bg-slate-950
                               dark:text-slate-300 dark:focus:ring-brand-500/15
                               sm:min-w-36"
                    >
                        <option value="">
                            همه جهت‌ها
                        </option>

                        @foreach($directionOptions as $directionOption)
                            <option value="{{ $directionOption->value }}">
                                {{ $directionOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Type filter --}}
                <div>
                    <label
                        for="wallet-transaction-type"
                        class="sr-only"
                    >
                        نوع تراکنش
                    </label>

                    <select
                        id="wallet-transaction-type"
                        wire:model.live="type"
                        class="h-10 w-full rounded-xl border border-slate-200
                               bg-slate-50 px-3 text-xs font-bold text-slate-700
                               outline-none transition
                               focus:border-brand-400 focus:bg-white
                               focus:ring-4 focus:ring-brand-100
                               dark:border-slate-700 dark:bg-slate-950
                               dark:text-slate-300 dark:focus:ring-brand-500/15
                               sm:min-w-44"
                    >
                        <option value="">
                            همه تراکنش‌ها
                        </option>

                        @foreach($typeOptions as $typeOption)
                            <option value="{{ $typeOption->value }}">
                                {{ $typeOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($direction !== '' || $type !== '')
            <div class="mt-3 flex justify-end">
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="text-xs font-bold text-slate-500 transition
                           hover:text-brand-700
                           dark:text-slate-400 dark:hover:text-brand-300"
                >
                    پاک‌کردن فیلترها
                </button>
            </div>
        @endif
    </div>

    {{-- Loading --}}
    <div
        wire:loading.flex
        wire:target="direction,type,resetFilters"
        class="items-center justify-center gap-2
               border-b border-slate-100 px-5 py-3
               text-xs font-semibold text-slate-500
               dark:border-slate-800 dark:text-slate-400"
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

        در حال دریافت تراکنش‌ها...
    </div>

    {{-- Transactions --}}
    <div wire:loading.class="opacity-60">
        @forelse($transactions as $transaction)
            @php
                $isCredit = $transaction->isCredit();

                $referenceLabel = match (true) {
                    $transaction->reference instanceof Order =>
                        'سفارش ' . $transaction->reference->order_number,

                    $transaction->reference instanceof WalletTopUp =>
                        'درخواست شارژ کیف پول',

                    default => null,
                };

                $transactionCode = substr(
                    (string) $transaction->public_token,
                    -10,
                );
            @endphp

            <article
                wire:key="wallet-transaction-{{ $transaction->id }}"
                class="border-b border-slate-100 p-4 last:border-b-0
                       sm:p-5
                       dark:border-slate-800"
            >
                <div
                    class="flex flex-col gap-4
                           sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex min-w-0 items-start gap-3">
                        {{-- Icon --}}
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center
                                   rounded-2xl
                                   {{ $isCredit
                                        ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300'
                                        : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' }}"
                        >
                            @if($isCredit)
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 19V5m-5 5 5-5 5 5"
                                    />
                                </svg>
                            @else
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 5v14m5-5-5 5-5-5"
                                    />
                                </svg>
                            @endif
                        </div>

                        {{-- Information --}}
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3
                                    class="text-sm font-black text-slate-900
                                           dark:text-white"
                                >
                                    {{ $transaction->type->label() }}
                                </h3>

                                <span
                                    class="rounded-lg border px-2 py-1
                                           text-[10px] font-extrabold
                                           {{ $isCredit
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300'
                                                : 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300' }}"
                                >
                                    {{ $transaction->direction->label() }}
                                </span>
                            </div>

                            @if($transaction->description)
                                <p
                                    class="mt-1 line-clamp-2 text-xs leading-6
                                           text-slate-500 dark:text-slate-400"
                                >
                                    {{ $transaction->description }}
                                </p>
                            @endif

                            <div
                                class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1
                                       text-[11px] text-slate-400"
                            >
                                <span>
                                    {{ jalali_date($transaction->created_at) }}
                                </span>

                                @if($referenceLabel)
                                    <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>

                                    <span>
                                        {{ $referenceLabel }}
                                    </span>
                                @endif

                                <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>

                                <span dir="ltr">
                                    #{{ $transactionCode }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="shrink-0 sm:text-left">
                        <div
                            class="text-lg font-black
                                   {{ $isCredit
                                        ? 'text-emerald-600 dark:text-emerald-300'
                                        : 'text-red-600 dark:text-red-300' }}"
                            dir="ltr"
                        >
                            {{ $isCredit ? '+' : '-' }}
                            {{ number_format((int) $transaction->amount) }}

                            <span class="text-xs font-bold">
                                تومان
                            </span>
                        </div>

                        <p
                            class="mt-1 text-[11px] font-semibold
                                   text-slate-400"
                            dir="rtl"
                        >
                            موجودی بعد از تراکنش:

                            <span
                                class="font-extrabold text-slate-600
                                       dark:text-slate-300"
                                dir="ltr"
                            >
                                {{ number_format((int) $transaction->balance_after) }}
                                تومان
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Additional financial detail --}}
                @if((int) $transaction->withdrawable_amount > 0)
                    <div
                        class="mt-3 rounded-xl border border-slate-100
                               bg-slate-50 px-3 py-2
                               text-[11px] leading-5 text-slate-500
                               dark:border-slate-800 dark:bg-slate-950
                               dark:text-slate-400"
                    >
                        مبلغ مرتبط با موجودی قابل برداشت:

                        <strong
                            class="font-extrabold text-slate-700
                                   dark:text-slate-300"
                            dir="ltr"
                        >
                            {{ number_format((int) $transaction->withdrawable_amount) }}
                            تومان
                        </strong>
                    </div>
                @endif
            </article>
        @empty
            <div class="px-5 py-12 text-center">
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center
                           rounded-2xl bg-slate-100 text-slate-400
                           dark:bg-slate-800 dark:text-slate-500"
                >
                    <svg
                        class="h-7 w-7"
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
                    </svg>
                </div>

                <h3
                    class="mt-4 text-sm font-black text-slate-800
                           dark:text-slate-200"
                >
                    تراکنشی یافت نشد
                </h3>

                <p
                    class="mx-auto mt-2 max-w-sm text-xs leading-6
                           text-slate-500 dark:text-slate-400"
                >
                    @if($direction !== '' || $type !== '')
                        تراکنشی مطابق فیلترهای انتخاب‌شده وجود ندارد.
                    @else
                        بعد از اولین شارژ یا استفاده از کیف پول، تراکنش‌ها در این بخش نمایش داده می‌شوند.
                    @endif
                </p>

                @if($direction !== '' || $type !== '')
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="mt-4 text-xs font-extrabold text-brand-700
                               transition hover:text-brand-800
                               dark:text-brand-300"
                    >
                        نمایش همه تراکنش‌ها
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
        <div
            class="border-t border-slate-200 px-4 py-4
                   dark:border-slate-800"
        >
            {{ $transactions->links() }}
        </div>
    @endif
</section>
