@extends('admin.layout')

@section('title', 'تنظیمات عمومی')

@section('content')
    <x-admin.page>
        <div class="space-y-6">

            <x-admin.metabox
                title="اطلاعات پایه سایت"
            >
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="عنوان سایت"
                        required
                        hint="این عنوان در بخش‌های اصلی سایت نمایش داده می‌شود."
                    >
                        <x-admin.form.input
                            name="site_title"
                            placeholder="مثلاً مودینگ وی"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="عنوان فرعی"
                        hint="توضیح کوتاه یا شعار برند."
                    >
                        <x-admin.form.input
                            name="site_subtitle"
                            placeholder="مثلاً مرجع دانلود و توسعه پچ و ماد"
                        />
                    </x-admin.field>

                    <div class="lg:col-span-2">
                        <x-admin.field
                            label="فاوآیکون"
                            hint="ترجیحاً تصویر مربعی با ابعاد مناسب."
                        >
                            <x-admin.form.input
                                type="file"
                                name="favicon"
                            />
                        </x-admin.field>
                    </div>
                </div>
            </x-admin.metabox>

            <x-admin.metabox
                title="سامانه‌های پیامکی"
            >
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="سرویس پیامکی پیش‌فرض"
                        hint="فعلاً نمایشی است و بعداً به بک‌اند متصل می‌شود."
                    >
                        <x-admin.form.select
                            name="default_sms_provider"
                            :options="[
                            ['value' => 'kavenegar', 'label' => 'کاوه نگار'],
                            ['value' => 'ippanel', 'label' => 'IPPanel'],
                            ['value' => 'smsir', 'label' => 'SMS.ir'],
                        ]"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="وضعیت"
                    >
                        <x-admin.form.switch
                            name="sms_enabled"
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox
                title="درگاه‌های پرداخت"
            >
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="درگاه پیش‌فرض"
                        hint="فعلاً نمایشی است."
                    >
                        <x-admin.form.select
                            name="default_payment_gateway"
                            :options="[
                            ['value' => 'zarinpal', 'label' => 'زرین پال'],
                            ['value' => 'nextpay', 'label' => 'نکست پی'],
                            ['value' => 'idpay', 'label' => 'آیدی پی'],
                        ]"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="فعال بودن پرداخت"
                    >
                        <x-admin.form.switch
                            name="payment_enabled"
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>

        </div>
    </x-admin.page>
@endsection
