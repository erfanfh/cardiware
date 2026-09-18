@extends('admin.layout')

@section('title', 'گزارش استفاده از کدتخفیف ' . $coupon->code)

@section('content')
    <div class="space-y-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg font-black text-slate-900">گزارش استفاده از کدتخفیف</h1>
                        <x-admin.badge color="dark" size="md" dir="ltr">
                            {{ $coupon->code }}
                        </x-admin.badge>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('coupon.edit')
                        <x-admin.form.button
                            tag="a"
                            href="{{ route('admin.coupons.edit', $coupon) }}"
                            variant="secondary"
                        >
                            ویرایش کدتخفیف
                        </x-admin.form.button>
                    @endcan

                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.coupons.index') }}"
                        variant="secondary"
                    >
                        بازگشت به کدهای تخفیف
                    </x-admin.form.button>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">تعداد استفاده موفق</div>
                <div class="mt-2 text-2xl font-black text-slate-900">
                    {{ number_format($stats['redemptions_count']) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">کاربران یکتا</div>
                <div class="mt-2 text-2xl font-black text-brand-700">
                    {{ number_format($stats['unique_users_count']) }}
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">مجموع تخفیف داده‌شده</div>
                <div class="mt-2 whitespace-nowrap text-xl font-black text-emerald-700">
                    {{ number_format($stats['discount_amount']) }}
                    <span class="text-xs font-normal text-slate-500">تومان</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-500">آخرین استفاده</div>
                @if($stats['latest_redemption'])
                    <div class="mt-2 text-sm font-black text-slate-900">
                        {{ jalali_date($stats['latest_redemption'], 'Y/m/d') }}
                    </div>
                    <div class="mt-1 text-xs font-bold text-slate-500">
                        ساعت {{ jalali_date($stats['latest_redemption'], 'H:i') }}
                    </div>
                @else
                    <div class="mt-2 text-sm font-bold text-slate-400">هنوز استفاده نشده</div>
                @endif
            </div>
        </div>

        @include('admin.promotion.coupons.redemptions.partials.filters-panel')

        <div class="space-y-4">
            @include('admin.promotion.coupons.redemptions.partials.list-table')

            <x-admin.list.pagination :model="$redemptions"/>
        </div>
    </div>
@endsection
