@extends('admin.layout')

@section('title', 'تنظیمات عمومی')

@section('content')
    <x-admin.form action="{{ route('admin.settings.general.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">

                <x-admin.metabox title="اطلاعات پایه سایت">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان سایت"
                        >
                            <x-admin.form.input
                                name="title"
                                value="{{ get_setting('general', 'title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="توضیح کوتاه"
                        >
                            <x-admin.form.input
                                name="subtitle"
                                value="{{ get_setting('general', 'subtitle') }}"
                            />
                        </x-admin.field>

                        <div class="w-56 max-w-full">
                            <x-admin.field
                                label="فاوآیکون"
                            >
                                <x-admin.form.media-picker
                                    name="favicon"
                                    :mediaId="get_setting('general', 'favicon')->id"
                                />
                            </x-admin.field>
                        </div>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="سامانه‌های پیامکی">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <x-admin.field
                            label="سرویس پیش‌فرض"
                        >
                            <x-admin.form.select
                                name="default_sms_provider"
                                placeholder="انتخاب سرویس"
                                :options="[
                            \App\Infrastructure\Services\Sms\Providers\SmsirProvider::class => 'اس ام اس دات آی آر (SMS.ir)',
                            \App\Infrastructure\Services\Sms\Providers\MelliPayamakProvider::class => 'ملی پیامک',
                            \App\Infrastructure\Services\Sms\Providers\FarazSmsProvider::class => 'فراز اس ام اس',
                            ]"
                                value="{{ get_setting('general', 'default_sms_provider') }}"
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

        <x-admin.save-button/>
    </x-admin.form>
@endsection
