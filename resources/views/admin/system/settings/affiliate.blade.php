@extends('admin.layout')

@section('title', 'تنظیمات همکاری در فروش')

@section('content')
    @php
        $enabled = old('affiliate_enabled', get_setting('affiliate', 'affiliate_enabled', true));
        $commissionType = old('default_commission_type', get_setting('affiliate', 'default_commission_type', 'percent'));
        $commissionValue = old('default_commission_value', get_setting('affiliate', 'default_commission_value', 11));
        $attributionDays = old('default_attribution_days', get_setting('affiliate', 'default_attribution_days', 90));
        $cookieDays = old('default_cookie_days', get_setting('affiliate', 'default_cookie_days', 30));
        $minWithdrawal = old('default_min_withdrawal_amount', get_setting('affiliate', 'default_min_withdrawal_amount', 100000));
        $bonusEnabled = old('referral_bonus_enabled', get_setting('affiliate', 'referral_bonus_enabled', true));
        $referrerPoints = old('referral_bonus_referrer_points', get_setting('affiliate', 'referral_bonus_referrer_points', 1000));
        $refereePoints = old('referral_bonus_referee_points', get_setting('affiliate', 'referral_bonus_referee_points', 500));
        $bonusMinOrder = old('referral_bonus_min_order_amount', get_setting('affiliate', 'referral_bonus_min_order_amount', 100000));
    @endphp

    <x-admin.form action="{{ route('admin.settings.affiliate.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-5">
                <x-admin.form.validation-errors class="mb-6"/>

                <x-admin.metabox title="تنظیمات کلی همکاری در فروش">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <div class="text-sm font-bold text-slate-800">فعال بودن سیستم همکاری و معرفی</div>
                                    <p class="mt-1 text-xs leading-6 text-slate-500">
                                        در حالت غیرفعال، لینک‌های معرفی و کدهای همکار هیچ انتسابی ثبت نمی‌کنند.
                                    </p>
                                </div>

                                <x-admin.form.switch name="affiliate_enabled" :checked="$enabled"/>
                            </label>
                        </div>

                        <x-admin.field required label="نوع کمیسیون پیش‌فرض">
                            <x-admin.form.select
                                name="default_commission_type"
                                :value="$commissionType"
                                :options="['percent' => 'درصدی از مبلغ سفارش', 'fixed' => 'مبلغ ثابت (تومان)']"
                            />
                        </x-admin.field>

                        <x-admin.field
                            required
                            label="مقدار کمیسیون پیش‌فرض"
                            hint="در حالت درصدی حداکثر ۱۰۰ (پیش‌فرض ۱۱). برای هر همکار به‌صورت جداگانه هم قابل تغییر است."
                        >
                            <x-admin.form.input name="default_commission_value" type="number" min="0" step="1" dir="ltr" :value="$commissionValue"/>
                        </x-admin.field>

                        <x-admin.field
                            required
                            label="پنجره انتساب پس از ثبت‌نام (روز)"
                            hint="خریدهای کاربری که با لینک معرفی ثبت‌نام کرده، تا این تعداد روز کمیسیون دارد. پیش‌فرض ۹۰."
                        >
                            <x-admin.form.input name="default_attribution_days" type="number" min="1" max="365" step="1" dir="ltr" :value="$attributionDays"/>
                        </x-admin.field>

                        <x-admin.field
                            required
                            label="مدت کوکی لینک معرفی (روز)"
                            hint="بازدیدکننده‌ای که با لینک می‌آید تا این تعداد روز به همکار وصل می‌ماند. پیش‌فرض ۳۰."
                        >
                            <x-admin.form.input name="default_cookie_days" type="number" min="1" max="365" step="1" dir="ltr" :value="$cookieDays"/>
                        </x-admin.field>

                        <x-admin.field required label="حداقل مبلغ برداشت پیش‌فرض (تومان)">
                            <x-admin.form.input name="default_min_withdrawal_amount" type="number" min="0" step="1" dir="ltr" :value="$minWithdrawal"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="پاداش معرفی دوستان">
                    <div class="mb-4 rounded-2xl border border-brand-100 bg-brand-50/60 px-4 py-3 text-xs leading-7 text-slate-700">
                        پاداش معرفی جدا از کمیسیون است و فقط یک بار برای اولین خریدِ پرداخت‌شده هر کاربر معرفی‌شده پرداخت می‌شود:
                        امتیاز به معرف و امتیاز هدیه به خود خریدار. ثبت‌نام با موبایل یا ایمیل خودِ معرف پاداش ندارد.
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <div class="text-sm font-bold text-slate-800">فعال بودن پاداش معرفی</div>
                                    <p class="mt-1 text-xs leading-6 text-slate-500">
                                        در حالت غیرفعال فقط کمیسیون همکاری پرداخت می‌شود و امتیاز معرفی اهدا نمی‌گردد.
                                    </p>
                                </div>

                                <x-admin.form.switch name="referral_bonus_enabled" :checked="$bonusEnabled"/>
                            </label>
                        </div>

                        <x-admin.field required label="امتیاز پاداش معرف">
                            <x-admin.form.input name="referral_bonus_referrer_points" type="number" min="0" step="1" dir="ltr" :value="$referrerPoints"/>
                        </x-admin.field>

                        <x-admin.field required label="امتیاز هدیه خریدار معرفی‌شده">
                            <x-admin.form.input name="referral_bonus_referee_points" type="number" min="0" step="1" dir="ltr" :value="$refereePoints"/>
                        </x-admin.field>

                        <x-admin.field
                            required
                            label="حداقل مبلغ خرید برای پاداش (تومان)"
                            hint="سفارش‌های کوچک‌تر از این مبلغ پاداش معرفی فعال نمی‌کنند (ضد سوءاستفاده)."
                        >
                            <x-admin.form.input name="referral_bonus_min_order_amount" type="number" min="0" step="1" dir="ltr" :value="$bonusMinOrder"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.form.save-button label="ذخیره تنظیمات همکاری"/>
            </div>
        </x-admin.page>
    </x-admin.form>
@endsection
