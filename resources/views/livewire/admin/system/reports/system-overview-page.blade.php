@php
    $metricCards = [
        [
            'key' => 'new_users',
            'title' => 'کاربر ثبت نام شده',
            'description' => 'تعداد حساب‌های کاربری ایجادشده در بازه انتخابی، حتی اگر بعدا حذف شده باشند',
            'format' => 'number',
            'icon' => 'icons.user-multiple',
            'sentiment' => 'positive',
        ],
        [
            'key' => 'otp_messages',
            'title' => 'پیامک کد یکبار مصرف',
            'description' => 'تعداد رکوردهای OTP موبایلی ایجادشده در بازه؛ هر رکورد معادل یک درخواست ارسال کد یکبار مصرف است',
            'format' => 'number',
            'icon' => 'icons.message-done-01',
            'sentiment' => 'neutral',
        ],
        [
            'key' => 'unique_otp_recipients',
            'title' => 'گیرنده یکتای پیامک',
            'description' => 'تعداد شماره موبایل‌های یکتایی که در بازه انتخابی برای آنها کد یکبار مصرف ایجاد شده است',
            'format' => 'number',
            'icon' => 'icons.telephone',
            'sentiment' => 'positive',
        ],
        [
            'key' => 'new_comments',
            'title' => 'دیدگاه جدید',
            'description' => 'تمام دیدگاه‌ها و پاسخ‌هایی که در این بازه ثبت شده‌اند',
            'format' => 'number',
            'icon' => 'icons.comment-01',
            'sentiment' => 'positive',
        ],
        [
            'key' => 'new_tickets',
            'title' => 'تیکت ایجاد شده',
            'description' => 'تعداد تیکت‌های پشتیبانی ایجادشده در بازه انتخابی',
            'format' => 'number',
            'icon' => 'icons.customer-support',
            'sentiment' => 'neutral',
        ],
        [
            'key' => 'download_sessions',
            'title' => 'دانلود ثبت شده',
            'description' => 'تعداد نشست‌های دانلود منحصربه‌فرد ثبت‌شده برای فایل‌های خریداری‌شده',
            'format' => 'number',
            'icon' => 'icons.cloud-download',
            'sentiment' => 'positive',
        ],
    ];

    $secondaryMetrics = [
        ['key' => 'verified_new_users', 'title' => 'کاربر جدید با موبایل تاییدشده', 'format' => 'number'],
        ['key' => 'unverified_new_users', 'title' => 'کاربر جدید تاییدنشده', 'format' => 'number'],
        ['key' => 'mobile_verification_rate', 'title' => 'نرخ تایید موبایل کاربران جدید', 'format' => 'percent'],
        ['key' => 'otp_validation_attempts', 'title' => 'تلاش ناموفق ورود کد', 'format' => 'number'],
        ['key' => 'otp_with_validation_errors', 'title' => 'کد دارای خطای ورود', 'format' => 'number'],
        ['key' => 'approved_comments', 'title' => 'دیدگاه تاییدشده', 'format' => 'number'],
        ['key' => 'closed_tickets', 'title' => 'تیکت بسته‌شده در بازه', 'format' => 'number'],
        ['key' => 'unique_downloaders', 'title' => 'دانلودکننده یکتا', 'format' => 'number'],
    ];

    $snapshotItems = [
        ['key' => 'total_users', 'title' => 'کل حساب‌های ثبت شده'],
        ['key' => 'active_users', 'title' => 'کاربران فعال فعلی'],
        ['key' => 'inactive_users', 'title' => 'کاربران غیرفعال'],
        ['key' => 'deleted_users', 'title' => 'کاربران حذف شده'],
        ['key' => 'pending_comments', 'title' => 'دیدگاه در انتظار بررسی', 'route' => 'admin.comments.index'],
        ['key' => 'open_tickets', 'title' => 'تیکت باز فعلی', 'route' => 'admin.tickets.index'],
        ['key' => 'total_page_views', 'title' => 'کل بازدید ثبت شده محتوا'],
    ];

    $formatMetric = static function ($value, string $format): string {
        return match ($format) {
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
        <div
            class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-2xl">
            <span class="animate-spin text-orange-500 [&_svg]:h-5 [&_svg]:w-5">
                @include('icons.reload')
            </span>
            <span>در حال به‌روزرسانی گزارش...</span>
        </div>
    </div>

    <section class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div
            class="pointer-events-none absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-violet-50 via-violet-50/30 to-transparent"></div>
        <div
            class="pointer-events-none absolute -left-16 -top-16 h-44 w-44 rounded-full bg-violet-100/70 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-violet-100 bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">
                    <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                    مرکز گزارش‌های عمومی مودینگ وی
                </div>

                <h1 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                    کاربران، پیامک‌های OTP و فعالیت سامانه
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                    شاخص‌های غیرمالی سامانه شامل رشد کاربران، پیامک‌های کد یکبار مصرف، دیدگاه‌ها، تیکت‌های پشتیبانی و
                    دانلود فایل‌های خریداری‌شده در این صفحه کنار هم قرار گرفته‌اند.
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
                class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700"
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
                        wire:key="system-report-preset-{{ $rangePreset->value }}"
                        wire:click="setPreset('{{ $rangePreset->value }}')"
                        @class([
                            'h-10 rounded-xl border px-4 text-sm font-bold transition',
                            'border-violet-600 bg-violet-600 text-white shadow-sm' => $preset === $rangePreset->value,
                            'border-slate-200 bg-white text-slate-600 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700' => $preset !== $rangePreset->value,
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
                        class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100"
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
                        class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100"
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
        <section class="rounded-[24px] border border-violet-200 bg-violet-50/60 p-4 shadow-sm sm:p-5">
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
                    class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700"
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
                $isNeutral = $card['sentiment'] === 'neutral';
                $isPositive = !$isNeutral && $direction === 'up';
                $isNegative = !$isNeutral && $direction === 'down';
            @endphp

            <article
                class="relative z-0 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:z-30 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-black text-slate-700">{{ $card['title'] }}</h2>
                            <span class="group relative inline-flex cursor-help text-slate-400">
                                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.help-circle')</span>
                                <span
                                    class="pointer-events-none absolute bottom-full right-1/2 z-50 mb-2 hidden w-64 translate-x-1/2 rounded-xl bg-slate-900 px-3 py-2 text-xs font-medium leading-6 text-white shadow-2xl group-hover:block">
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
                                    'bg-slate-100 text-slate-600' => $isNeutral || (!$isPositive && !$isNegative),
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

                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-violet-50 text-violet-600">
                        <span class="[&_svg]:h-5 [&_svg]:w-5">@include($card['icon'])</span>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($secondaryMetrics as $item)
            @php($metric = $overview->metrics[$item['key']])
            <article class="rounded-[20px] border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div class="text-xs font-black text-slate-500">{{ $item['title'] }}</div>
                <div class="mt-2 text-lg font-black text-slate-900">
                    {{ $formatMetric($metric->value, $item['format']) }}
                </div>
                @if($metric->comparisonValue !== null)
                    <div class="mt-1 text-[11px] font-bold text-slate-400">
                        دوره قبل: {{ $formatMetric($metric->comparisonValue, $item['format']) }}
                    </div>
                @endif
            </article>
        @endforeach
    </section>

    <section
        x-data="systemReportsChart(@js($chartPayload))"
        x-init="init()"
        x-on:livewire:navigating.window="destroy()"
        class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm"
    >
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-black text-slate-900">روند فعالیت سامانه</h2>
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-600">
                        {{ $overview->filter->granularity->label() }}
                    </span>
                </div>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                    نوع گزارش را عوض کنید تا روند ثبت نام، پیامک‌های کد یکبار مصرف یا تعامل کاربران در همان بازه نمایش
                    داده شود.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1">
                    <button
                        type="button"
                        @click="setMode('users')"
                        :class="mode === 'users' ? 'bg-white text-violet-700 shadow-sm' : 'text-slate-500'"
                        class="h-8 rounded-lg px-3 text-xs font-black transition"
                    >
                        کاربران
                    </button>
                    <button
                        type="button"
                        @click="setMode('otp')"
                        :class="mode === 'otp' ? 'bg-white text-violet-700 shadow-sm' : 'text-slate-500'"
                        class="h-8 rounded-lg px-3 text-xs font-black transition"
                    >
                        پیامک OTP
                    </button>
                    <button
                        type="button"
                        @click="setMode('engagement')"
                        :class="mode === 'engagement' ? 'bg-white text-violet-700 shadow-sm' : 'text-slate-500'"
                        class="h-8 rounded-lg px-3 text-xs font-black transition"
                    >
                        تعامل
                    </button>
                </div>

                <button
                    type="button"
                    @click="download()"
                    class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700"
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
            class="mx-4 mb-4 flex flex-col gap-2 rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3 text-xs font-bold text-slate-600 sm:flex-row sm:items-center sm:justify-between"
        >
            <span>
                نقطه انتخاب شده:
                <strong class="text-slate-900" x-text="activePoint?.label"></strong>
            </span>
            <span>
                <strong class="text-violet-700" x-text="activePoint?.series"></strong>:
                <span x-text="formatActiveValue()"></span>
            </span>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm xl:col-span-8">
            <div class="border-b border-slate-100 p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900">گزارش پیامک‌های کد یکبار مصرف</h2>
                        <p class="mt-1 text-xs leading-6 text-slate-500">
                            آمار مستقیما از رکوردهای موبایلی جدول OTP استخراج شده و براساس نوع عملیات تفکیک شده است.
                        </p>
                    </div>
                    <div class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                        {{ number_format($overview->metrics['otp_messages']->value) }} پیامک OTP
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-right text-sm">
                    <thead class="bg-slate-50 text-xs font-black text-slate-500">
                    <tr>
                        <th class="px-5 py-4">نوع کد</th>
                        <th class="px-4 py-4">تعداد پیامک</th>
                        <th class="px-4 py-4">گیرنده یکتا</th>
                        <th class="px-4 py-4">تلاش ناموفق ورود کد</th>
                        <th class="px-4 py-4">کد دارای خطا</th>
                        <th class="px-5 py-4">نرخ خطای ورود</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($overview->otpTypes as $otpType)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-5 py-4 font-black text-slate-800">{{ $otpType['label'] }}</td>
                            <td class="px-4 py-4 font-bold text-slate-700">{{ number_format($otpType['messages_count']) }}</td>
                            <td class="px-4 py-4 font-bold text-slate-600">{{ number_format($otpType['unique_recipients']) }}</td>
                            <td class="px-4 py-4 font-bold text-amber-700">{{ number_format($otpType['validation_attempts']) }}</td>
                            <td class="px-4 py-4 font-bold text-rose-700">{{ number_format($otpType['messages_with_validation_errors']) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-rose-400"
                                            style="width: {{ min(100, max(0, $otpType['validation_error_rate'])) }}%"
                                        ></div>
                                    </div>
                                    <span class="font-black text-slate-700">{{ number_format($otpType['validation_error_rate'], 1) }}٪</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm font-bold text-slate-400">
                                در این بازه پیامک OTP ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm xl:col-span-4">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-base font-black text-slate-900">وضعیت فعلی سامانه</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">
                    این اعداد لحظه‌ای هستند و به بازه زمانی انتخاب شده وابسته نیستند.
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($snapshotItems as $item)
                    @if(isset($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-slate-50">
                            <span class="text-xs font-bold text-slate-500">{{ $item['title'] }}</span>
                            <span
                                class="text-sm font-black text-slate-900">{{ number_format($overview->systemSnapshot[$item['key']]) }}</span>
                        </a>
                    @else
                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs font-bold text-slate-500">{{ $item['title'] }}</span>
                            <span
                                class="text-sm font-black text-slate-900">{{ number_format($overview->systemSnapshot[$item['key']]) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm xl:col-span-1">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-base font-black text-slate-900">محصولات با بیشترین دانلود</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">براساس نشست‌های دانلود ثبت‌شده در بازه</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($overview->topDownloadedProducts as $index => $product)
                    <div class="flex items-center gap-3 p-4 transition hover:bg-slate-50/80">
                        <div
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-sm font-black text-slate-600">
                            {{ $index + 1 }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-black text-slate-800">{{ $product['title'] }}</div>
                            <div class="mt-1 text-xs font-bold text-slate-400">
                                {{ number_format($product['unique_users']) }} کاربر یکتا
                            </div>
                        </div>
                        <div class="shrink-0 text-sm font-black text-sky-700">
                            {{ number_format($product['downloads_count']) }}
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm font-bold text-slate-400">
                        در این بازه دانلودی ثبت نشده است.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-base font-black text-slate-900">وضعیت کاربران جدید</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">وضعیت تایید شماره موبایل حساب‌های ایجادشده در بازه</p>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-emerald-50 p-4 text-center">
                        <div class="text-2xl font-black text-emerald-700">
                            {{ number_format($overview->metrics['verified_new_users']->value) }}
                        </div>
                        <div class="mt-1 text-xs font-bold text-emerald-700/70">تاییدشده</div>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-4 text-center">
                        <div class="text-2xl font-black text-amber-700">
                            {{ number_format($overview->metrics['unverified_new_users']->value) }}
                        </div>
                        <div class="mt-1 text-xs font-bold text-amber-700/70">تاییدنشده</div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3 text-xs font-black text-slate-500">
                        <span>نرخ تایید موبایل</span>
                        <span class="text-sm text-slate-900">{{ number_format($overview->metrics['mobile_verification_rate']->value, 1) }}٪</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                        <div
                            class="h-full rounded-full bg-emerald-500"
                            style="width: {{ min(100, max(0, $overview->metrics['mobile_verification_rate']->value)) }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </article>

        <article class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-base font-black text-slate-900">وضعیت تعامل و پشتیبانی</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500">وضعیت فعلی آیتم‌هایی که در همین بازه ایجاد شده‌اند</p>
            </div>

            <div class="p-5">
                <div class="text-xs font-black text-slate-500">دیدگاه‌ها</div>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-emerald-50 p-3 text-center">
                        <div
                            class="text-lg font-black text-emerald-700">{{ number_format($overview->commentStatuses['approved']) }}</div>
                        <div class="mt-1 text-[11px] font-bold text-emerald-700/70">تاییدشده</div>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3 text-center">
                        <div
                            class="text-lg font-black text-amber-700">{{ number_format($overview->commentStatuses['pending']) }}</div>
                        <div class="mt-1 text-[11px] font-bold text-amber-700/70">در انتظار</div>
                    </div>
                    <div class="rounded-xl bg-rose-50 p-3 text-center">
                        <div
                            class="text-lg font-black text-rose-700">{{ number_format($overview->commentStatuses['rejected']) }}</div>
                        <div class="mt-1 text-[11px] font-bold text-rose-700/70">ردشده</div>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-5 text-xs font-black text-slate-500">تیکت‌ها</div>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-amber-50 p-3 text-center">
                        <div
                            class="text-lg font-black text-amber-700">{{ number_format($overview->ticketStatuses['open']) }}</div>
                        <div class="mt-1 text-[11px] font-bold text-amber-700/70">باز</div>
                    </div>
                    <div class="rounded-xl bg-sky-50 p-3 text-center">
                        <div
                            class="text-lg font-black text-sky-700">{{ number_format($overview->ticketStatuses['answered']) }}</div>
                        <div class="mt-1 text-[11px] font-bold text-sky-700/70">پاسخ داده</div>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-3 text-center">
                        <div
                            class="text-lg font-black text-emerald-700">{{ number_format($overview->ticketStatuses['closed']) }}</div>
                        <div class="mt-1 text-[11px] font-bold text-emerald-700/70">بسته</div>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <div
        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-bold text-slate-400 shadow-sm">
        <span>
            آخرین محاسبه: {{ \Morilog\Jalali\Jalalian::fromCarbon($overview->generatedAt->setTimezone(config('app.timezone'))->toMutable())->format('Y/m/d H:i:s') }}
        </span>
    </div>
</div>
