@php
    $isEdit = $affiliate->exists;
    $commissionTypes = [
        'percent' => 'درصدی از مبلغ سفارش',
        'fixed' => 'مبلغ ثابت (تومان)',
    ];
@endphp

@extends('admin.layout')

@section('title', $isEdit ? 'ویرایش همکار در فروش' : 'ایجاد همکار در فروش')

@section('content')
    <x-admin.page>
        <x-admin.form.validation-errors class="mb-6"/>

        <x-admin.form action="{{ $isEdit ? route('admin.affiliates.update', $affiliate) : route('admin.affiliates.store') }}"
                      :method="$isEdit ? 'put' : 'post'">
            @if($isEdit)
                @method('PUT')
            @endif

            <x-admin.metabox title="مشخصات همکار">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @if(! $isEdit)
                        <x-admin.field label="شناسه کاربر (ID)" required hint="شناسه کاربری که می‌خواهید همکار شود؛ از لیست کاربران قابل کپی است." :error="$errors->first('user_id')">
                            <x-admin.form.input type="number" min="1" step="1" name="user_id" :value="old('user_id')" dir="ltr"/>
                        </x-admin.field>
                    @else
                        <x-admin.field label="همکار">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700">
                                {{ $affiliate->user?->first_name }} {{ $affiliate->user?->last_name }}
                                <span class="text-xs font-normal text-slate-400" dir="ltr">({{ $affiliate->user?->email }})</span>
                            </div>
                        </x-admin.field>
                    @endif

                    <x-admin.field label="کد همکار" required hint="حروف انگلیسی/عدد. اگر کوپنی با همین کد بسازید، خرید با آن کوپن هم به این همکار منتسب می‌شود." :error="$errors->first('code')">
                        <x-admin.form.input type="text" name="code" :value="old('code', $affiliate->code)" dir="ltr" placeholder="MWABC123"/>
                    </x-admin.field>

                    <x-admin.field label="نوع کمیسیون" required :error="$errors->first('commission_type')">
                        <x-admin.form.select name="commission_type" :value="old('commission_type', $affiliate->commission_type?->value)" :options="$commissionTypes"/>
                    </x-admin.field>

                    <x-admin.field label="مقدار کمیسیون" required hint="در حالت درصدی بین ۰ تا ۱۰۰ (پیش‌فرض ۱۱)؛ در حالت ثابت، مبلغ تومانی هر سفارش." :error="$errors->first('commission_value')">
                        <x-admin.form.input type="number" min="0" step="0.01" name="commission_value" :value="old('commission_value', $affiliate->commission_value)" dir="ltr"/>
                    </x-admin.field>

                    <x-admin.field label="مدت کوکی انتساب (روز)" required hint="بازدیدکننده‌ای که با لینک می‌آید به مدت این روزها به همکار وصل می‌ماند." :error="$errors->first('cookie_days')">
                        <x-admin.form.input type="number" min="1" max="365" step="1" name="cookie_days" :value="old('cookie_days', $affiliate->cookie_days ?? 30)" dir="ltr"/>
                    </x-admin.field>

                    <x-admin.field label="پنجره انتساب پس از ثبت‌نام (روز)" required hint="خریدهای کاربرِ معرفی‌شده تا این تعداد روز بعد از ثبت‌نام، کمیسیون دارند." :error="$errors->first('attribution_days')">
                        <x-admin.form.input type="number" min="1" max="365" step="1" name="attribution_days" :value="old('attribution_days', $affiliate->attribution_days ?? 90)" dir="ltr"/>
                    </x-admin.field>

                    <x-admin.field label="حداقل مبلغ برداشت (تومان)" required :error="$errors->first('min_withdrawal_amount')">
                        <x-admin.form.input type="number" min="0" step="1" name="min_withdrawal_amount" :value="old('min_withdrawal_amount', $affiliate->min_withdrawal_amount ?? 100000)" dir="ltr"/>
                    </x-admin.field>

                    <x-admin.field label="شروع فعالیت" hint="اختیاری؛ خالی یعنی بدون محدودیت.">
                        <x-admin.form.date-picker name="starts_at" :value="old('starts_at', $affiliate->starts_at ? jalali_date($affiliate->starts_at) : '')"/>
                    </x-admin.field>

                    <x-admin.field label="پایان فعالیت" hint="اختیاری؛ خالی یعنی بدون محدودیت.">
                        <x-admin.form.date-picker name="ends_at" :value="old('ends_at', $affiliate->ends_at ? jalali_date($affiliate->ends_at) : '')"/>
                    </x-admin.field>

                    <x-admin.field label="یادداشت مدیر">
                        <x-admin.form.input type="text" name="notes" :value="old('notes', $affiliate->notes)"/>
                    </x-admin.field>

                    <div class="md:col-span-2">
                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <div class="text-sm font-bold text-slate-800">فعال بودن همکار</div>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    همکار غیرفعال نه کلیک ثبت می‌کند، نه ثبت‌نام و سفارشی به او منتسب می‌شود.
                                </p>
                            </div>

                            <x-admin.form.switch
                                name="is_active"
                                :checked="old('is_active', $affiliate->is_active ?? true)"
                            />
                        </label>
                    </div>
                </div>
            </x-admin.metabox>

            <x-admin.form.save-button :label="$isEdit ? 'ذخیره تغییرات' : 'ایجاد همکار'"/>
        </x-admin.form>
    </x-admin.page>
@endsection
