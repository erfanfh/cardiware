@extends('admin.layout')

@section('title', 'سبدهای خرید رها شده')

@section('content')
    <div class="space-y-5">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-lg font-black text-slate-900">سبدهای خرید رها شده</h1>
                    <p class="mt-1 max-w-3xl text-sm leading-7 text-slate-500">
                        در این صفحه سبدهای <span class="font-bold text-slate-700">ACTIVE</span> دارای آیتم نمایش داده
                        می‌شوند؛
                        یعنی سبدهایی که هنوز وارد مرحله Checkout و پرداخت نشده‌اند. برای سبد رها شده وضعیت جدیدی در
                        دیتابیس ایجاد نمی‌شود.
                    </p>
                </div>

                <div class="grid shrink-0 grid-cols-1 gap-2 sm:grid-cols-2">
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-6 text-amber-800">
                        مهمان:
                        <span class="font-black">حذف بعد از {{ $guestRetentionDays }} روز</span>
                    </div>
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-xs leading-6 text-indigo-800">
                        عضو:
                        <span class="font-black">حذف بعد از {{ $userRetentionMonths }} ماه</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400">کل سبدهای فعال دارای آیتم</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stats['count']) }}</div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400">کاربران مهمان</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stats['guest_count']) }}</div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400">کاربران عضو</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($stats['user_count']) }}</div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-xs font-bold text-slate-400">ارزش فعلی سبدها</div>
                <div class="mt-2 whitespace-nowrap text-xl font-black text-slate-900">
                    {{ number_format($stats['total_amount']) }}
                    <span class="text-xs font-normal text-slate-500">تومان</span>
                </div>
            </div>

            <div class="rounded-3xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
                <div class="text-xs font-bold text-rose-500">آماده پاکسازی</div>
                <div class="mt-2 text-2xl font-black text-rose-700">{{ number_format($stats['ready_for_cleanup']) }}</div>
                <div class="mt-1 text-[10px] leading-5 text-rose-500">
                    {{ number_format($stats['guest_ready_for_cleanup']) }} مهمان
                    / {{ number_format($stats['user_ready_for_cleanup']) }} عضو
                </div>
            </div>
        </div>

        <x-admin.metabox
                title="تنظیمات پاکسازی خودکار"
                :collapsible="false"
                class="!overflow-visible"
        >
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 xl:items-end">
                <div class="xl:col-span-7">
                    <x-admin.form
                            method="put"
                            action="{{ route('admin.abandoned-carts.settings.update') }}"
                    >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5 xl:items-end">
                            <div class="xl:col-span-2">
                                <label for="guest_retention_days"
                                       class="mb-2 block text-sm font-extrabold text-slate-700">
                                    نگهداری سبد کاربران مهمان
                                </label>

                                <div class="flex items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <x-admin.form.input
                                                id="guest_retention_days"
                                                type="number"
                                                name="guest_retention_days"
                                                :value="old('guest_retention_days', $guestRetentionDays)"
                                                min="1"
                                                max="365"
                                                required
                                        />
                                    </div>
                                    <span class="shrink-0 text-sm font-bold text-slate-500">روز</span>
                                </div>

                                @error('guest_retention_days')
                                <div class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="xl:col-span-2">
                                <label for="user_retention_months"
                                       class="mb-2 block text-sm font-extrabold text-slate-700">
                                    نگهداری سبد کاربران عضو
                                </label>

                                <div class="flex items-center gap-2">
                                    <div class="min-w-0 flex-1">
                                        <x-admin.form.input
                                                id="user_retention_months"
                                                type="number"
                                                name="user_retention_months"
                                                :value="old('user_retention_months', $userRetentionMonths)"
                                                min="1"
                                                max="120"
                                                required
                                        />
                                    </div>
                                    <span class="shrink-0 text-sm font-bold text-slate-500">ماه</span>
                                </div>

                                @error('user_retention_months')
                                <div class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="xl:col-span-1">
                                <x-admin.form.button type="submit" variant="solid"
                                                     class="w-full cursor-pointer justify-center">
                                    ذخیره
                                </x-admin.form.button>
                            </div>
                        </div>
                    </x-admin.form>
                </div>

                <div class="xl:col-span-3"></div>

                <div class="xl:col-span-2 xl:text-left">
                    <x-admin.form
                            method="post"
                            action="{{ route('admin.abandoned-carts.cleanup') }}"
                            x-on:submit.prevent="if (confirm('سبدهای ACTIVE قدیمی بر اساس بازه مهمان و عضو پاک شوند؟')) $el.submit()"
                    >
                        <x-admin.form.button type="submit" variant="danger"
                                             class="w-full cursor-pointer justify-center">
                            پاکسازی الان
                        </x-admin.form.button>
                    </x-admin.form>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs leading-7 text-blue-700">
                Scheduler هر روز ساعت <span class="font-black text-blue-800">03:30</span> اجرا می‌شود.
                مهمان‌های بدون تغییر از قبل از
                <span class="font-black text-blue-800">{{ jalali_date($guestCleanupCutoff, 'Y/m/d') }}</span>
                و اعضای بدون تغییر از قبل از
                <span class="font-black text-blue-800">{{ jalali_date($userCleanupCutoff, 'Y/m/d') }}</span>
                پاک می‌شوند.
            </div>
        </x-admin.metabox>

        @include('admin.commerce.abandoned-carts.partials.filters-panel')

        <div class="space-y-4">
            @include('admin.commerce.abandoned-carts.partials.list-table')
            <x-admin.list.pagination :model="$carts"/>
        </div>
    </div>
@endsection
