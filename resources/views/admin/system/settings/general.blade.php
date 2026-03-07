@extends('admin.layout')

@section('title', 'تنظیمات عمومی')

@section('content')
    <x-admin.page>
        <div class="space-y-6">

            <x-admin.metabox title="اطلاعات پایه سایت">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="عنوان سایت"
                        hint="این عنوان در بخش‌های مختلف سایت نمایش داده می‌شود."
                        required
                    >
                        <x-admin.form.input
                            name="site_title"
                            placeholder="مثلاً مودینگ وی"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="عنوان فرعی"
                        hint="شعار یا توضیح کوتاه برند."
                    >
                        <x-admin.form.input
                            name="site_subtitle"
                            placeholder="مثلاً مرجع دانلود و توسعه پچ و ماد"
                        />
                    </x-admin.field>

                    <div class="lg:col-span-2">
                        <x-admin.field
                            label="فاوآیکون"
                            hint="ترجیحاً تصویر مربعی و سبک."
                        >
                            <x-admin.form.input
                                type="file"
                                name="favicon"
                            />
                        </x-admin.field>
                    </div>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="سامانه‌های پیامکی">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="سرویس پیش‌فرض"
                        hint="فعلاً فقط نمای ظاهری فرم است."
                    >
                        <x-admin.form.select
                            name="default_sms_provider"
                            placeholder="انتخاب سرویس"
                            :options="[
                            'kavenegar' => 'کاوه نگار',
                            'ippanel' => 'IPPanel',
                            'smsir' => 'SMS.ir',
                        ]"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="فعال بودن سامانه پیامکی"
                    >
                        <x-admin.form.switch
                            name="sms_enabled"
                            :checked="true"
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="درگاه‌های پرداخت">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="درگاه پیش‌فرض"
                        hint="فعلاً نمایشی است و بعداً به بک‌اند وصل می‌شود."
                    >
                        <x-admin.form.select
                            name="default_payment_gateway"
                            placeholder="انتخاب درگاه"
                            :options="[
                            'zarinpal' => 'زرین‌پال',
                            'nextpay' => 'نکست‌پی',
                            'idpay' => 'آیدی‌پی',
                        ]"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="فعال بودن پرداخت"
                    >
                        <x-admin.form.switch
                            name="payment_enabled"
                            :checked="true"
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>

        </div>
    </x-admin.page>
@endsection
