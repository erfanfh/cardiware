@extends('admin.layout')

@section('title', 'برترین فروشندگان و جوایز')

@section('content')
    @php
        $places = ['اول', 'دوم', 'سوم'];
        $toman = fn ($value) => number_format((int) $value) . ' تومان';
    @endphp
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="max-w-3xl text-sm leading-7 text-slate-500">
                هر شب هفته، ماه و سالِ تمام‌شده بسته می‌شود و سه فروشگاه با بیشترین فروش خالص (سفارش‌های مرجوع‌شده حساب نمی‌شوند؛ فقط محصولاتی که سهم فروشنده دارند)
                جایزه می‌گیرند: سهم بیشتر از هر فروش (کمیسیون کمتر) برای مدتی، کد تخفیف شخصی و نمایش محصولات در «فروشگاه ویژه» صفحه اصلی.
            </p>
            <form method="post" action="{{ route('admin.seller-rewards.close') }}">
                @csrf
                <button type="submit" class="inline-flex h-10 items-center rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white hover:bg-brand-600">بررسی دوره‌های تمام‌شده</button>
            </form>
        </div>

        <x-admin.metabox title="رقابت جاری">
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach($live as $type => $block)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="text-xs font-extrabold text-slate-500">{{ $block['period']->label }}</div>
                        <ol class="mt-3 space-y-2 text-sm">
                            @forelse($block['rows'] as $row)
                                <li class="flex justify-between gap-2">
                                    <span class="truncate font-bold">{{ $loop->iteration }}. {{ $row->title }}</span>
                                    <span class="shrink-0 text-slate-500">{{ $toman($row->net_sales) }}</span>
                                </li>
                            @empty
                                <li class="text-xs text-slate-400">هنوز فروشی ثبت نشده</li>
                            @endforelse
                        </ol>
                        <div class="mt-2 text-[11px] text-slate-400">حداقل فروش برای جایزه: {{ $toman($minNetSales[$type]) }}</div>
                    </div>
                @endforeach
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="فروشگاه ویژه صفحه اصلی">
            <p class="mb-3 text-xs leading-6 text-slate-500">جایزه‌ی برندگان خودکار اینجا اضافه می‌شود؛ می‌توانید فروشگاهی را دستی هم انتخاب کنید (انتخاب دستی اولویت دارد). جایگاه «فروشگاه ویژه» در چیدمان صفحه اصلی است.</p>
            @if($spotlights->isNotEmpty())
                <div class="mb-4 space-y-2">
                    @foreach($spotlights as $spotlight)
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                            <span>
                                <b>{{ $spotlight->store?->title }}</b>
                                — {{ $spotlight->source === 'reward' ? 'جایزه: ' . $spotlight->headline : 'انتخاب مدیر' }}
                                — از {{ jalali_date($spotlight->starts_at) }} تا {{ jalali_date($spotlight->ends_at) }}
                                {{ $spotlight->starts_at->isFuture() ? '(رزرو)' : '(در حال نمایش)' }}
                            </span>
                            <form method="post" action="{{ route('admin.seller-rewards.spotlights.destroy', $spotlight) }}" class="js-confirm" data-confirm-title="حذف فروشگاه ویژه" data-confirm-text="این فروشگاه از جایگاه «فروشگاه ویژه» برداشته می‌شود.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1 text-xs font-bold text-red-600">حذف</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            <form method="post" action="{{ route('admin.seller-rewards.spotlights.store') }}" class="grid gap-3 md:grid-cols-4 md:items-end">
                @csrf
                <x-admin.field label="فروشگاه">
                    <x-admin.form.select name="store_id" :options="['' => 'انتخاب فروشگاه'] + $stores->all()"/>
                </x-admin.field>
                <x-admin.field label="چند روز">
                    <x-admin.form.input type="number" name="days" min="1" max="90" value="7"/>
                </x-admin.field>
                <x-admin.field label="عنوان (اختیاری)">
                    <x-admin.form.input name="headline" placeholder="مثلاً: پیشنهاد ویژه این هفته"/>
                </x-admin.field>
                <button type="submit" class="h-11 rounded-xl bg-emerald-600 px-4 text-xs font-extrabold text-white">نمایش در صفحه اصلی</button>
            </form>
        </x-admin.metabox>

        <x-admin.metabox title="برندگان دوره‌های قبل">
            @if($closures->isEmpty())
                <p class="text-sm text-slate-500">هنوز دوره‌ای بسته نشده است.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                            <th class="px-3 py-3">دوره</th><th class="px-3 py-3">رتبه</th><th class="px-3 py-3">فروشگاه</th>
                            <th class="px-3 py-3">فروش خالص</th><th class="px-3 py-3">جوایز</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($closures as $closure)
                            @forelse($closure->rewards as $reward)
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2.5 font-bold">{{ $loop->first ? $closure->period_label : '' }}</td>
                                    <td class="px-3 py-2.5">{{ $reward->trophy() }} {{ $reward->rank }}</td>
                                    <td class="px-3 py-2.5 font-bold">{{ $reward->store?->title }}</td>
                                    <td class="px-3 py-2.5">{{ $toman($reward->net_sales) }} ({{ number_format($reward->orders_count) }} سفارش)</td>
                                    <td class="px-3 py-2.5 text-xs leading-6 text-slate-600">
                                        @if($reward->commission_bonus_bp) +{{ number_format($reward->commission_bonus_bp / 100, 1) }}٪ سهم فروش تا {{ jalali_date($reward->bonus_ends_at) }} @endif
                                        @if($reward->coupon_code) · کد {{ $reward->coupon_code }} @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-slate-100"><td class="px-3 py-2.5 font-bold">{{ $closure->period_label }}</td><td colspan="4" class="px-3 py-2.5 text-xs text-slate-400">برنده‌ای نداشت</td></tr>
                            @endforelse
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $closures->links() }}</div>
            @endif
        </x-admin.metabox>

        <form method="post" action="{{ route('admin.seller-rewards.settings') }}">
            @csrf
            @method('PUT')
            <x-admin.metabox title="تنظیمات جوایز">
                <div class="mb-4 grid gap-4 md:grid-cols-2">
                    <x-admin.field label="جوایز فروشندگان فعال باشد" class="switch-field">
                        <input type="hidden" name="enabled" value="0">
                        <x-admin.form.switch name="enabled" :checked="$enabled"/>
                    </x-admin.field>
                    <x-admin.field label="عنوان جایگاه دستی در صفحه اصلی">
                        <x-admin.form.input name="spotlight_title" :value="$spotlightTitle"/>
                    </x-admin.field>
                </div>

                <x-admin.field
                    class="mb-4"
                    label="فروشگاه‌های خارج از رقابت"
                    hint="فروشگاه اصلی سایت را این‌جا انتخاب کنید تا در رتبه‌بندی برترین فروشنده شرکت نکند و رقابت فقط بین فروشندگان واقعی باشد."
                >
                    <select name="excluded_store_ids[]" multiple size="8"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                        @foreach($stores as $storeId => $storeTitle)
                            <option value="{{ $storeId }}"
                                @selected(in_array((string) $storeId, array_map('strval', $excludedStoreIds), true))>
                                {{ $storeTitle }} (#{{ $storeId }})
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-2 text-[11px] leading-5 text-slate-500">
                        با Ctrl (یا Cmd) چند فروشگاه را هم‌زمان انتخاب کنید. فروشگاه اصلی معمولاً
                        <code dir="ltr">moddingway</code> است.
                    </p>
                </x-admin.field>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                        <tr class="border-b border-slate-200 text-right text-slate-400">
                            <th class="px-2 py-2">دوره / رتبه</th>
                            <th class="px-2 py-2">سهم اضافه (صدم درصد؛ ۲۰۰ = ۲٪)</th>
                            <th class="px-2 py-2">مدت سهم اضافه (روز)</th>
                            <th class="px-2 py-2">درصد کد تخفیف</th>
                            <th class="px-2 py-2">سقف تخفیف</th>
                            <th class="px-2 py-2">اعتبار کد (روز)</th>
                            <th class="px-2 py-2">صفحه اصلی (روز)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(\App\Infrastructure\Services\Setting\Groups\SellerRewardSettings::PERIODS as $type => $typeLabel)
                            @foreach(range(0, 2) as $index)
                                <tr class="border-b border-slate-100">
                                    <td class="px-2 py-2 font-bold">{{ $typeLabel }} — نفر {{ $places[$index] }}</td>
                                    @foreach(['commission_bonus_bp', 'bonus_days', 'coupon_percent', 'coupon_max_amount', 'coupon_days', 'spotlight_days'] as $field)
                                        <td class="px-2 py-1.5">
                                            <input type="number" min="0" dir="ltr" name="prizes[{{ $type }}][{{ $index }}][{{ $field }}]"
                                                   value="{{ old("prizes.$type.$index.$field", $prizes[$type][$index][$field] ?? 0) }}"
                                                   class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-xs">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach(\App\Infrastructure\Services\Setting\Groups\SellerRewardSettings::PERIODS as $type => $typeLabel)
                        <x-admin.field label="حداقل فروش خالص {{ $typeLabel }} برای جایزه (تومان)">
                            <x-admin.form.input type="number" min="0" name="min_net_sales[{{ $type }}]" :value="old('min_net_sales.' . $type, $minNetSales[$type])"/>
                        </x-admin.field>
                    @endforeach
                </div>
            </x-admin.metabox>
            <x-admin.form.save-button label="ذخیره تنظیمات جوایز"/>
        </form>
    </x-admin.page>
@endsection
