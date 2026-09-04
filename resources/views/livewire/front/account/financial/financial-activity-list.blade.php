@php
    use App\Domain\Financial\Enums\FinancialActivityDirectionEnum;
    use App\Domain\Financial\Enums\FinancialActivityStatusEnum;
    use App\Domain\Financial\Enums\FinancialActivityTypeEnum;
@endphp

<div
    id="financial-activities-list"
    class="scroll-mt-36"
>
    <x-front.cart class="!overflow-hidden !p-0">
        {{-- Header and filters --}}
        <div class="border-b border-slate-200 p-4 sm:p-5 dark:border-slate-800">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-black text-slate-900 dark:text-white whitespace-nowrap">
                        تراکنش های مالی
                    </h2>

                    <span
                        class="rounded-lg bg-slate-100 px-2 py-1 whitespace-nowrap
                               text-[10px] font-black text-slate-500
                               dark:bg-slate-800 dark:text-slate-400"
                    >
                        {{ number_format($activities->total()) }} مورد
                    </span>
                </div>

                {{-- Filters --}}
                <div
                    class="grid grid-cols-2 gap-2
                       sm:grid-cols-4 xl:w-auto"
                >
                    {{-- Type --}}
                    <div>
                        <label for="financial-activity-type" class="sr-only">
                            نوع تراکنش
                        </label>

                        <select
                            id="financial-activity-type"
                            wire:model.live="type"
                            class="h-10 w-full cursor-pointer rounded-xl
                               border border-slate-200 bg-slate-50 px-3
                               text-xs font-bold text-slate-700 outline-none
                               transition focus:border-brand-400 focus:bg-white
                               focus:ring-4 focus:ring-brand-100
                               dark:border-slate-700 dark:bg-slate-950
                               dark:text-slate-300 dark:focus:bg-slate-900 dark:focus:ring-brand-500/15
                               xl:min-w-40"
                        >
                            <option value="">
                                همه تراکنش ها
                            </option>

                            @foreach($typeOptions as $typeOption)
                                <option value="{{ $typeOption->value }}">
                                    {{ $typeOption->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="financial-activity-status" class="sr-only">
                            وضعیت تراکنش
                        </label>

                        <select
                            id="financial-activity-status"
                            wire:model.live="status"
                            class="h-10 w-full cursor-pointer rounded-xl
                               border border-slate-200 bg-slate-50 px-3
                               text-xs font-bold text-slate-700 outline-none
                               transition focus:border-brand-400 focus:bg-white
                               focus:ring-4 focus:ring-brand-100
                               dark:border-slate-700 dark:bg-slate-950
                               dark:text-slate-300 dark:focus:bg-slate-900 dark:focus:ring-brand-500/15
                               xl:min-w-36"
                        >
                            <option value="">
                                همه وضعیت ها
                            </option>

                            @foreach($statusOptions as $statusOption)
                                <option value="{{ $statusOption->value }}">
                                    {{ $statusOption->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Direction --}}
                    <div>
                        <label for="financial-activity-direction" class="sr-only">
                            جهت تراکنش
                        </label>

                        <select
                            id="financial-activity-direction"
                            wire:model.live="direction"
                            class="h-10 w-full cursor-pointer rounded-xl
                               border border-slate-200 bg-slate-50 px-3
                               text-xs font-bold text-slate-700 outline-none
                               transition focus:border-brand-400 focus:bg-white
                               focus:ring-4 focus:ring-brand-100
                               dark:border-slate-700 dark:bg-slate-950
                               dark:text-slate-300 dark:focus:bg-slate-900 dark:focus:ring-brand-500/15
                               xl:min-w-32"
                        >
                            <option value="">
                                همه جهت ها
                            </option>

                            @foreach($directionOptions as $directionOption)
                                <option value="{{ $directionOption->value }}">
                                    {{ $directionOption->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Per page --}}
                    <div>
                        <label for="financial-activity-per-page" class="sr-only">
                            تعداد نمایش
                        </label>

                        <select
                            id="financial-activity-per-page"
                            wire:model.live="perPage"
                            class="h-10 w-full cursor-pointer rounded-xl
                               border border-slate-200 bg-slate-50 px-3
                               text-xs font-bold text-slate-700 outline-none
                               transition focus:border-brand-400 focus:bg-white
                               focus:ring-4 focus:ring-brand-100
                               dark:border-slate-700 dark:bg-slate-950
                               dark:text-slate-300 dark:focus:bg-slate-900 dark:focus:ring-brand-500/15
                               xl:min-w-28"
                        >
                            @foreach($perPageOptions as $perPageOption)
                                <option value="{{ $perPageOption }}">
                                    {{ $perPageOption }} مورد
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @if($hasActiveFilters)
                <div class="mt-3 flex justify-end">
                    <button
                        type="button"
                        wire:click="resetFilters"
                        wire:loading.attr="disabled"
                        class="cursor-pointer text-xs font-bold
                           text-slate-500 transition hover:text-brand-700
                           disabled:cursor-not-allowed disabled:opacity-60
                           dark:text-slate-400 dark:hover:text-brand-300"
                    >
                        پاک کردن فیلترها
                    </button>
                </div>
            @endif
        </div>

        {{-- Loading --}}
        <div
            wire:loading.delay.flex
            wire:target="
        type,
        status,
        direction,
        perPage,
        resetFilters,
        setPage,
        gotoPage,
        nextPage,
        previousPage
    "
            style="display: none;"
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

        {{-- Activities --}}
        <div wire:loading.class.delay="pointer-events-none opacity-60">
            @forelse($activities as $activity)
                @php
                    $hasFinancialEffect = $activity->hasFinancialEffect();
                    $isCredit = $activity->isCredit();
                    $isDebit = $activity->isDebit();

                    $statusClasses = match ($activity->status) {
                        FinancialActivityStatusEnum::SUCCESSFUL =>
                            'border-emerald-200 bg-emerald-50 text-emerald-700
                             dark:border-emerald-900/50 dark:bg-emerald-900/20
                             dark:text-emerald-300',

                        FinancialActivityStatusEnum::PENDING =>
                            'border-amber-200 bg-amber-50 text-amber-700
                             dark:border-amber-900/50 dark:bg-amber-900/20
                             dark:text-amber-300',

                        FinancialActivityStatusEnum::FAILED =>
                            'border-red-200 bg-red-50 text-red-700
                             dark:border-red-900/50 dark:bg-red-900/20
                             dark:text-red-300',

                        FinancialActivityStatusEnum::CANCELLED =>
                            'border-slate-200 bg-slate-100 text-slate-600
                             dark:border-slate-700 dark:bg-slate-800
                             dark:text-slate-300',

                        FinancialActivityStatusEnum::EXPIRED =>
                            'border-orange-200 bg-orange-50 text-orange-700
                             dark:border-orange-900/50 dark:bg-orange-900/20
                             dark:text-orange-300',

                        FinancialActivityStatusEnum::REFUNDED =>
                            'border-sky-200 bg-sky-50 text-sky-700
                             dark:border-sky-900/50 dark:bg-sky-900/20
                             dark:text-sky-300',
                    };

                    $iconClasses = match (true) {
                        $isCredit =>
                            'bg-emerald-50 text-emerald-600
                             dark:bg-emerald-500/10 dark:text-emerald-300',

                        $isDebit =>
                            'bg-red-50 text-red-600
                             dark:bg-red-500/10 dark:text-red-300',

                        $activity->status === FinancialActivityStatusEnum::PENDING =>
                            'bg-amber-50 text-amber-600
                             dark:bg-amber-500/10 dark:text-amber-300',

                        $activity->status->isFailed() =>
                            'bg-slate-100 text-slate-500
                             dark:bg-slate-800 dark:text-slate-400',

                        default =>
                            'bg-brand-50 text-brand-600
                             dark:bg-brand-500/10 dark:text-brand-300',
                    };

                    $amountClasses = match (true) {
                        $isCredit =>
                            'text-emerald-600 dark:text-emerald-300',

                        $isDebit =>
                            'text-red-600 dark:text-red-300',

                        default =>
                            'text-slate-800 dark:text-slate-200',
                    };

                    $hasExtraDetails =
                        count($activity->details) > 0
                        || filled($activity->trackingCode)
                        || filled($activity->referenceLabel)
                        || filled($activity->publicToken)
                        || $activity->balanceAfter !== null;

                    $detailLabels = [
                        'gateway' => 'درگاه پرداخت',
                        'payment_status' => 'وضعیت پرداخت',
                        'tax_amount' => 'مالیات',
                        'gateway_reference' => 'شناسه مرجع درگاه',
                        'gateway_transaction_id' => 'شناسه تراکنش درگاه',
                        'gateway_message' => 'پیام درگاه',
                        'purpose' => 'نوع شارژ',
                        'top_up_status' => 'وضعیت شارژ',
                        'wallet_amount' => 'پرداخت از کیف پول',
                        'gateway_amount' => 'پرداخت از درگاه',
                        'withdrawable_amount' => 'مبلغ قابل برداشت',
                        'failure_reason' => 'علت ناموفق بودن',
                        'counterparty_mobile' => 'شماره طرف مقابل',
                        'transfer_status' => 'وضعیت انتقال',
                        'converted_points' => 'امتیاز مصرف‌شده',
                    ];

                    $amountDetailKeys = [
                        'tax_amount',
                        'wallet_amount',
                        'gateway_amount',
                        'withdrawable_amount',
                    ];
                @endphp

                <article
                    wire:key="{{ $activity->key }}"
                    x-data="{ detailsOpen: false }"
                    class="border-b border-slate-100 p-4 last:border-b-0
                       sm:p-5 dark:border-slate-800"
                >
                    <div
                        class="flex flex-col gap-4
                           sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="flex min-w-0 items-start gap-3">
                            {{-- Icon --}}
                            <div
                                class="flex h-11 w-11 shrink-0 items-center
                                   justify-center rounded-2xl {{ $iconClasses }}"
                            >
                                @if($activity->type === FinancialActivityTypeEnum::LOYALTY_CONVERSION)
                                    <span class="[&_svg]:h-5 [&_svg]:w-5">@include('icons.award-03')</span>
                                @elseif($activity->type === FinancialActivityTypeEnum::WALLET_TRANSFER)
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
                                @elseif($activity->type === FinancialActivityTypeEnum::ORDER_PAYMENT)
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
                                            d="M7.5 15.75h3"
                                        />
                                    </svg>
                                @elseif($activity->type === FinancialActivityTypeEnum::WALLET_TOP_UP)
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
                                @elseif($activity->status === FinancialActivityStatusEnum::PENDING)
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle cx="12" cy="12" r="8.25"></circle>

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 7.5V12l3 2"
                                        />
                                    </svg>
                                @elseif($isCredit)
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

                            {{-- Main information --}}
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="text-sm font-black
                                           text-slate-900 dark:text-white"
                                    >
                                        {{ $activity->title }}
                                    </h3>

                                    <span
                                        class="rounded-lg border px-2 py-1
                                           text-[10px] font-extrabold
                                           {{ $statusClasses }}"
                                    >
                                    {{ $activity->status->label() }}
                                </span>

                                    @if($activity->hasFinancialEffect())
                                        <span
                                            class="rounded-lg border px-2 py-1
                                               text-[10px] font-extrabold

                                               {{ $isCredit
                                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300'
                                                    : 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300' }}"
                                        >
                                        {{ $activity->direction?->label() }}
                                    </span>
                                    @endif
                                </div>

                                @if($activity->description)
                                    <p
                                        class="mt-1 line-clamp-2 text-xs leading-6
                                           text-slate-500 dark:text-slate-400"
                                    >
                                        {{ $activity->description }}
                                    </p>
                                @endif

                                <div
                                    class="mt-2 flex flex-wrap items-center
                                       gap-x-3 gap-y-1 text-[11px]
                                       text-slate-400"
                                >
                                <span>
                                    {{ jalali_date($activity->occurredAt) }}
                                </span>

                                    @if($activity->referenceLabel)
                                        <span
                                            class="hidden h-1 w-1 rounded-full
                                               bg-slate-300 sm:block
                                               dark:bg-slate-700"
                                        ></span>

                                        @if($activity->referenceUrl)
                                            <a
                                                href="{{ $activity->referenceUrl }}"
                                                class="font-bold text-brand-600
                                                   transition hover:text-brand-700
                                                   dark:text-brand-300"
                                            >
                                                {{ $activity->referenceLabel }}
                                            </a>
                                        @else
                                            <span>
                                            {{ $activity->referenceLabel }}
                                        </span>
                                        @endif
                                    @endif

                                    @if($activity->transactionCode())
                                        <span
                                            class="hidden h-1 w-1 rounded-full
                                               bg-slate-300 sm:block
                                               dark:bg-slate-700"
                                        ></span>

                                        <span dir="ltr">
                                        #{{ $activity->transactionCode() }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div class="shrink-0 sm:min-w-52 sm:text-left">
                            <div
                                class="text-lg font-black {{ $amountClasses }}"
                                dir="ltr"
                            >
                                @if($activity->amountSign())
                                    {{ $activity->amountSign() }}
                                @endif

                                {{ number_format($activity->amount) }}

                                <span class="text-xs font-bold">
                                تومان
                            </span>
                            </div>

                            @if($activity->balanceAfter !== null)
                                <p
                                    class="mt-1 text-[11px] font-semibold
                                       text-slate-400"
                                    dir="rtl"
                                >
                                    موجودی بعد از تراکنش:

                                    <span
                                        class="font-extrabold
                                           text-slate-600 dark:text-slate-300"
                                        dir="ltr"
                                    >
                                    {{ number_format($activity->balanceAfter) }}
                                    تومان
                                </span>
                                </p>
                            @endif

                            @if($hasExtraDetails)
                                <button
                                    type="button"
                                    x-on:click="detailsOpen = !detailsOpen"
                                    class="mt-2 inline-flex cursor-pointer
                                       items-center gap-1 text-[11px]
                                       font-extrabold text-brand-600
                                       transition hover:text-brand-700
                                       dark:text-brand-300
                                       dark:hover:text-brand-200"
                                >
                                <span x-text="detailsOpen ? 'بستن جزئیات' : 'نمایش جزئیات'">
                                    نمایش جزئیات
                                </span>

                                    <svg
                                        class="h-3.5 w-3.5 transition-transform"
                                        x-bind:class="detailsOpen && 'rotate-180'"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m6 9 6 6 6-6"
                                        />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Expanded details --}}
                    @if($hasExtraDetails)
                        <div
                            x-show="detailsOpen"
                            x-collapse
                            x-cloak
                        >
                            <div
                                class="mt-4 rounded-2xl border
                                   border-slate-200 bg-slate-50 p-3
                                   dark:border-slate-800 dark:bg-slate-950"
                            >
                                <div
                                    class="grid gap-x-6 gap-y-3
                                       sm:grid-cols-2 lg:grid-cols-3"
                                >
                                    @if($activity->trackingCode)
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400">
                                                کد پیگیری
                                            </p>

                                            <p
                                                class="mt-1 break-all text-xs font-black
                                                   text-slate-700 dark:text-slate-300"
                                                dir="ltr"
                                            >
                                                {{ $activity->trackingCode }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($activity->transactionCode())
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400">
                                                کد تراکنش
                                            </p>

                                            <p
                                                class="mt-1 text-xs font-black
                                                   text-slate-700 dark:text-slate-300"
                                                dir="ltr"
                                            >
                                                #{{ $activity->transactionCode() }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($activity->referenceLabel)
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400">
                                                مرجع
                                            </p>

                                            <p
                                                class="mt-1 text-xs font-black
                                                   text-slate-700 dark:text-slate-300"
                                            >
                                                {{ $activity->referenceLabel }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($activity->balanceAfter !== null)
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400">
                                                موجودی بعد از تراکنش
                                            </p>

                                            <p
                                                class="mt-1 text-xs font-black
                                                   text-slate-700 dark:text-slate-300"
                                                dir="ltr"
                                            >
                                                {{ number_format($activity->balanceAfter) }}
                                                تومان
                                            </p>
                                        </div>
                                    @endif

                                    @foreach($activity->details as $detailKey => $detailValue)
                                        @continue(blank($detailValue))

                                        <div
                                            @class([
                                                'sm:col-span-2 lg:col-span-3' => in_array(
                                                    $detailKey,
                                                    [
                                                        'gateway_message',
                                                        'failure_reason',
                                                    ],
                                                    true,
                                                ),
                                            ])
                                        >
                                            <p class="text-[10px] font-bold text-slate-400">
                                                {{ $detailLabels[$detailKey] ?? $detailKey }}
                                            </p>

                                            <p
                                                class="mt-1 break-words text-xs font-black
                                                   leading-6 text-slate-700
                                                   dark:text-slate-300"
                                                @if(
                                                    in_array(
                                                        $detailKey,
                                                        [
                                                            'gateway_reference',
                                                            'gateway_transaction_id',
                                                            'counterparty_mobile',
                                                        ],
                                                        true,
                                                    )
                                                )
                                                    dir="ltr"
                                                @endif
                                            >
                                                @if(
                                                    in_array(
                                                        $detailKey,
                                                        $amountDetailKeys,
                                                        true,
                                                    )
                                                )
                                                    {{ number_format((int) $detailValue) }}
                                                    تومان
                                                @else
                                                    {{ $detailValue }}
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </article>
            @empty
                <div class="px-5 py-14 text-center">
                    <div
                        class="mx-auto flex h-14 w-14 items-center
                           justify-center rounded-2xl bg-slate-100
                           text-slate-400 dark:bg-slate-800
                           dark:text-slate-500"
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

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8.25 12h7.5"
                            />
                        </svg>
                    </div>

                    <h3
                        class="mt-4 text-sm font-black
                           text-slate-800 dark:text-slate-200"
                    >
                        تراکنشی پیدا نشد
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-sm text-xs leading-6
                           text-slate-500 dark:text-slate-400"
                    >
                        @if($hasActiveFilters)
                            هیچ تراکنشی با فیلترهای انتخاب شده مطابقت ندارد.
                        @else
                            بعد از انجام اولین عملیات مالی، اطلاعات آن در این بخش نمایش داده می‌شود.
                        @endif
                    </p>

                    @if($hasActiveFilters)
                        <button
                            type="button"
                            wire:click="resetFilters"
                            class="mt-4 cursor-pointer text-xs
                               font-extrabold text-brand-700
                               transition hover:text-brand-800
                               dark:text-brand-300"
                        >
                            نمایش همه تراکنش ها
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($activities->hasPages())
            <div
                class="border-t border-slate-200 px-4 py-4 dark:border-slate-800"
            >
                {{ $activities
                    ->onEachSide(1)
                    ->links(data: [
                        'scrollTo' => '#financial-activities-list',
                    ])
                }}
            </div>
        @endif
    </x-front.cart>
</div>
