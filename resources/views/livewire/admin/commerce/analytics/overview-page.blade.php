@php
    $metricCards = [
        [
            'key' => 'net_sales',
            'title' => 'فروش خالص',
            'description' => 'مبلغ خالص سفارش‌های پرداخت‌شده پس از تخفیف و بدون مالیات',
            'format' => 'money',
            'icon' => 'icons.chart-03',
        ],
        [
            'key' => 'gross_sales',
            'title' => 'فروش ناخالص',
            'description' => 'ارزش محصولات و افزونه‌ها قبل از تخفیف',
            'format' => 'money',
            'icon' => 'icons.transaction',
        ],
        [
            'key' => 'paid_orders_count',
            'title' => 'سفارش پرداخت‌شده',
            'description' => 'تعداد سفارش‌هایی که در بازه انتخابی پرداخت شده‌اند',
            'format' => 'number',
            'icon' => 'icons.invoice-03',
        ],
        [
            'key' => 'average_order_value',
            'title' => 'میانگین ارزش سفارش',
            'description' => 'فروش خالص تقسیم بر تعداد سفارش‌های پرداخت‌شده',
            'format' => 'money',
            'icon' => 'icons.shopping-cart-02',
        ],
        [
            'key' => 'unique_customers',
            'title' => 'خریدار یکتا',
            'description' => 'تعداد کاربران یکتایی که در این بازه خرید موفق داشته‌اند',
            'format' => 'number',
            'icon' => 'icons.user-multiple',
        ],
        [
            'key' => 'external_payment_success_rate',
            'title' => 'نرخ موفقیت درگاه',
            'description' => 'پرداخت‌های خارجی موفق از میان تلاش‌های ایجادشده در بازه',
            'format' => 'percent',
            'icon' => 'icons.credit-card',
        ],
    ];

    $secondaryMetrics = [
        [
            'key' => 'tax_amount',
            'title' => 'مالیات وصول‌شده',
            'format' => 'money',
        ],
        [
            'key' => 'customer_paid_amount',
            'title' => 'کل مبلغ پرداختی سفارش‌ها با مالیات',
            'format' => 'money',
        ],
        [
            'key' => 'discount_amount',
            'title' => 'کل تخفیف',
            'format' => 'money',
        ],
        [
            'key' => 'product_discount_amount',
            'title' => 'تخفیف خود محصولات',
            'format' => 'money',
        ],
        [
            'key' => 'coupon_discount_amount',
            'title' => 'تخفیف کد تخفیف',
            'format' => 'money',
        ],
        [
            'key' => 'product_units_sold',
            'title' => 'تعداد آیتم اصلی فروخته‌شده',
            'format' => 'number',
        ],
        [
            'key' => 'addons_sold',
            'title' => 'تعداد افزونه فروخته‌شده',
            'format' => 'number',
        ],
        [
            'key' => 'sales_external_cash_inflow',
            'title' => 'ورودی نقدی مرتبط با فروش',
            'format' => 'money',
        ],
        [
            'key' => 'manual_wallet_top_up_cash_inflow',
            'title' => 'شارژ مستقیم کیف پول',
            'format' => 'money',
        ],
        [
            'key' => 'external_cash_inflow',
            'title' => 'کل ورود نقدی از درگاه‌ها',
            'format' => 'money',
        ],
    ];

    $formatMetric = static function ($value, string $format): string {
        return match ($format) {
            'money' => number_format((int)$value) . ' تومان',
            'percent' => number_format((float)$value, 1) . '٪',
            default => number_format((int)$value),
        };
    };
@endphp

<div
        class="relative space-y-6 [&_button]:cursor-pointer"
        wire:loading.class="pointer-events-none select-none"
        wire:target="setPreset,refreshReport,applyCustomRange,comparison,granularity"
        aria-live="polite"
>
    <div
            wire:loading.flex
            wire:target="setPreset,refreshReport,applyCustomRange,comparison,granularity"
            class="fixed inset-0 z-[100] hidden items-start justify-center bg-slate-950/10 px-4 pt-24 backdrop-blur-[1px]"
            role="status"
            aria-label="در حال به‌روزرسانی گزارش"
    >
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-2xl">
            <span class="animate-spin text-orange-500 [&_svg]:h-5 [&_svg]:w-5">
                @include('icons.reload')
            </span>

            <span>در حال به‌روزرسانی گزارش...</span>
        </div>
    </div>
    <section class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="pointer-events-none absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-orange-50 via-orange-50/30 to-transparent"></div>
        <div class="pointer-events-none absolute -left-16 -top-16 h-44 w-44 rounded-full bg-orange-100/60 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-orange-100 bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                    <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                    مرکز تحلیل مودینگ وی
                </div>

                <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    تحلیل فروش، سفارش و جریان پرداخت
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                    اعداد فروش بر اساس زمان پرداخت موفق سفارش محاسبه می‌شوند. ورود پول از درگاه، شارژ کیف پول و فروش
                    سفارش عمدا از یکدیگر جدا شده‌اند تا هیچ مبلغی دوباره شمرده نشود.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                    <span class="rounded-xl bg-slate-100 px-3 py-2">بازه: {{ $rangeLabel }}</span>

                    @if($comparisonRangeLabel)
                        <span class="rounded-xl bg-violet-50 px-3 py-2 text-violet-700">
                            مقایسه با: {{ $comparisonRangeLabel }}
                        </span>
                    @endif
                </div>
            </div>

            <button
                    type="button"
                    wire:click="refreshReport"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                <span class="[&_svg]:h-5 [&_svg]:w-5" wire:loading.class="animate-spin"
                      wire:target="refreshReport,setPreset,applyCustomRange,comparison,granularity">
                    @include('icons.reload')
                </span>
                به‌روزرسانی گزارش
            </button>
        </div>
    </section>

    <section class="sticky top-0 z-10 rounded-[24px] border border-slate-200 bg-white/95 p-4 shadow-sm backdrop-blur">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-wrap gap-2">
                @foreach($presets as $rangePreset)
                    <button
                            type="button"
                            wire:key="analytics-preset-{{ $rangePreset->value }}"
                            wire:click="setPreset('{{ $rangePreset->value }}')"
                            @class([
                                'h-10 rounded-xl border px-4 text-sm font-bold transition',
                                'border-orange-500 bg-orange-500 text-white shadow-sm' => $preset === $rangePreset->value,
                                'border-slate-200 bg-white text-slate-600 hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700' => $preset !== $rangePreset->value,
                            ])
                    >
                        {{ $rangePreset->label() }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <label class="flex items-center gap-2">
                    <span class="hidden text-xs font-bold text-slate-500 sm:inline">نمایش:</span>
                    <select
                            wire:model.live="granularity"
                            class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-orange-300 focus:ring-2 focus:ring-orange-100"
                    >
                        @foreach($granularityOptions as $granularityOption)
                            <option value="{{ $granularityOption->value }}">
                                {{ $granularityOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="flex items-center gap-2">
                    <span class="hidden text-xs font-bold text-slate-500 sm:inline">مقایسه:</span>
                    <select
                            wire:model.live="comparison"
                            class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-orange-300 focus:ring-2 focus:ring-orange-100"
                    >
                        @foreach($comparisonOptions as $comparisonOption)
                            <option value="{{ $comparisonOption->value }}">
                                {{ $comparisonOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </section>

    @if($preset === \App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum::CUSTOM->value)
        <section class="rounded-[24px] border border-orange-200 bg-orange-50/60 p-4 shadow-sm sm:p-5">
            <form wire:submit="applyCustomRange" class="flex flex-col gap-4 xl:flex-row xl:items-end">
                <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-black text-slate-600">از تاریخ</span>
                        <x-admin.form.date-picker
                                wire:model="draftCustomFrom"
                                data-jdp-only-date
                                placeholder="مثلا ۱۴۰۵/۰۵/۰۱"
                        />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-black text-slate-600">تا تاریخ</span>
                        <x-admin.form.date-picker
                                wire:model="draftCustomTo"
                                data-jdp-only-date
                                placeholder="مثلا ۱۴۰۵/۰۵/۳۰"
                        />
                    </label>
                </div>

                <button
                        type="submit"
                        class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-black text-white shadow-sm transition hover:bg-orange-600"
                >
                    <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.filter')</span>
                    اعمال بازه
                </button>
            </form>

            @error('customRange')
            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">
                {{ $message }}
            </div>
            @enderror

            <p class="mt-3 text-xs font-medium leading-6 text-slate-500">
                حداکثر بازه سفارشی سه سال است. تاریخ پایان به صورت کامل تا انتهای همان روز محاسبه می‌شود.
            </p>
        </section>
    @endif

    @if(
        $overview->filter->requestedGranularity !== \App\Application\Commerce\Analytics\Enums\AnalyticsGranularityEnum::AUTO
        && $overview->filter->requestedGranularity !== $overview->filter->granularity
    )
        <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs font-bold leading-6 text-sky-800">
            برای جلوگیری از شلوغی نمودار، دانه‌بندی «{{ $overview->filter->requestedGranularity->label() }}» به صورت
            خودکار به «{{ $overview->filter->granularity->label() }}» تغییر کرد.
        </div>
    @endif

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($metricCards as $card)
            @php
                $metric = $overview->metrics[$card['key']];
                $direction = $metric->direction();
                $isPositive = $direction === 'up';
                $isNegative = $direction === 'down';
            @endphp

            <article
                    class="relative z-0 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:z-30 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-black text-slate-700">{{ $card['title'] }}</h2>

                            <span class="group relative inline-flex cursor-help text-slate-400">
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.help-circle')</span>
                                <span class="pointer-events-none absolute bottom-full right-1/2 z-50 mb-2 hidden w-64 translate-x-1/2 rounded-xl bg-slate-900 px-3 py-2 text-xs font-medium leading-6 text-white shadow-2xl group-hover:block">
                                    {{ $card['description'] }}
                                </span>
                            </span>
                        </div>

                        <div class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                            {{ $formatMetric($metric->value, $card['format']) }}
                        </div>

                        @if($metric->comparisonValue !== null)
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-bold">
                                <span @class([
                                    'inline-flex items-center gap-1 rounded-lg px-2 py-1',
                                    'bg-emerald-50 text-emerald-700' => $isPositive,
                                    'bg-rose-50 text-rose-700' => $isNegative,
                                    'bg-slate-100 text-slate-600' => !$isPositive && !$isNegative,
                                ])>
                                    @if($metric->changePercentage === null)
                                        رشد از مقدار صفر
                                    @else
                                        {{ $metric->changePercentage > 0 ? '+' : '' }}{{ number_format($metric->changePercentage, 1) }}
                                        ٪
                                    @endif
                                </span>

                                <span class="text-slate-400">
                                    دوره قبل: {{ $formatMetric($metric->comparisonValue, $card['format']) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <span class="[&_svg]:h-6 [&_svg]:w-6">
                            @include($card['icon'])
                        </span>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($secondaryMetrics as $item)
            @php($metric = $overview->metrics[$item['key']])

            <article class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">{{ $item['title'] }}</div>
                <div class="mt-2 text-xl font-black text-slate-900">
                    {{ $formatMetric($metric->value, $item['format']) }}
                </div>

                @if($metric->comparisonValue !== null)
                    <div class="mt-2 text-xs font-bold text-slate-400">
                        دوره قبل: {{ $formatMetric($metric->comparisonValue, $item['format']) }}
                    </div>
                @endif
            </article>
        @endforeach
    </section>

    <section
            wire:key="commerce-sales-chart-{{ $overview->filter->cacheKey() }}-{{ $overview->generatedAt->format('Uu') }}"
            wire:ignore
            x-data="commerceAnalyticsChart(@js($chartPayload))"
            x-init="init()"
            class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm"
    >
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-black text-slate-900">روند فروش و سفارش‌ها</h2>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-600">
                        {{ $overview->filter->granularity->label() }}
                    </span>
                </div>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                    با حرکت روی نمودار جزئیات هر نقطه را ببینید؛ از نوار پایین نیز میتوانید بازه قابل نمایش را جابه‌جا
                    کنید.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                    <button
                            type="button"
                            @click="setMode('sales')"
                            :class="mode === 'sales' ? 'bg-white text-orange-700 shadow-sm' : 'text-slate-500'"
                            class="h-8 rounded-lg px-3 text-xs font-black transition"
                    >
                        مبلغ فروش
                    </button>
                    <button
                            type="button"
                            @click="setMode('orders')"
                            :class="mode === 'orders' ? 'bg-white text-orange-700 shadow-sm' : 'text-slate-500'"
                            class="h-8 rounded-lg px-3 text-xs font-black transition"
                    >
                        تعداد سفارش
                    </button>
                </div>

                <button
                        type="button"
                        @click="download()"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-600 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
                >
                    <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.download-01')</span>
                    خروجی تصویر
                </button>
            </div>
        </div>

        <div class="p-2 sm:p-4">
            <div x-ref="chart" class="h-[360px] w-full sm:h-[430px]"></div>
        </div>

        <div
                x-show="activePoint"
                x-cloak
                class="mx-4 mb-4 flex flex-col gap-2 rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-xs font-bold text-slate-600 sm:flex-row sm:items-center sm:justify-between"
        >
            <span>
                نقطه انتخاب شده:
                <strong class="text-slate-900" x-text="activePoint?.label"></strong>
            </span>
            <span>
                <strong class="text-orange-700" x-text="activePoint?.series"></strong>:
                <span x-text="formatActiveValue()"></span>
            </span>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm xl:col-span-7">
            <div class="border-b border-slate-100 p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900">عملکرد درگاه‌های پرداخت</h2>
                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            تلاش‌ها بر اساس زمان ایجاد Payment و مبلغ موفق بر اساس وضعیت نهایی همان Payment نمایش داده
                            شده است.
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                        {{ number_format($overview->metrics['external_payment_attempts']->value) }} تلاش خارجی
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-right text-sm">
                    <thead class="bg-slate-50 text-xs font-black text-slate-500">
                    <tr>
                        <th class="px-5 py-4">درگاه</th>
                        <th class="px-4 py-4">تلاش</th>
                        <th class="px-4 py-4">موفق</th>
                        <th class="px-4 py-4">ناموفق</th>
                        <th class="px-4 py-4">باز</th>
                        <th class="px-4 py-4">نرخ موفقیت</th>
                        <th class="px-5 py-4">مبلغ پرداخت موفق</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($overview->gatewayPerformance as $gateway)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4 font-black text-slate-800">{{ $gateway['label'] }}</td>
                            <td class="px-4 py-4 font-bold text-slate-600">{{ number_format($gateway['attempts_count']) }}</td>
                            <td class="px-4 py-4 font-bold text-emerald-700">{{ number_format($gateway['successful_count']) }}</td>
                            <td class="px-4 py-4 font-bold text-rose-700">{{ number_format($gateway['unsuccessful_count']) }}</td>
                            <td class="px-4 py-4 font-bold text-amber-700">{{ number_format($gateway['open_count']) }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                                class="h-full rounded-full bg-emerald-500"
                                                style="width: {{ min(100, max(0, $gateway['success_rate'])) }}%"
                                        ></div>
                                    </div>
                                    <span class="font-black text-slate-700">{{ number_format($gateway['success_rate'], 1) }}٪</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-black text-slate-900">
                                {{ number_format($gateway['paid_amount']) }} تومان
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm font-bold text-slate-400">
                                در این بازه تلاش پرداخت خارجی ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm xl:col-span-5">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-base font-black text-slate-900">محصولات برتر بر اساس فروش خالص</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                    مبلغ افزونه‌های وابسته نیز به محصول اصلی تخصیص داده شده است.
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($overview->topProducts as $index => $product)
                    <div class="flex items-center gap-3 p-4 transition hover:bg-slate-50/80">
                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-sm font-black text-slate-600">
                            {{ $index + 1 }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-black text-slate-800">{{ $product['title'] }}</div>
                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs font-bold text-slate-400">
                                <span>{{ number_format($product['orders_count']) }} سفارش</span>
                                <span>{{ number_format($product['sold_quantity']) }} فروش آیتم اصلی</span>
                            </div>
                        </div>

                        <div class="shrink-0 text-left">
                            <div class="text-sm font-black text-slate-900">
                                {{ number_format($product['net_sales']) }}
                            </div>
                            <div class="mt-1 text-[11px] font-bold text-slate-400">تومان</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm font-bold text-slate-400">
                        در این بازه محصول فروخته‌شده‌ای وجود ندارد.
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-bold text-slate-400 shadow-sm">
        <span>
            آخرین محاسبه: {{ \Morilog\Jalali\Jalalian::fromCarbon($overview->generatedAt->setTimezone(config('app.timezone'))->toMutable())->format('Y/m/d H:i:s') }}
        </span>
    </div>
</div>
