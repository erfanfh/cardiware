<div class="space-y-6">
    <x-admin.metabox title="سامانه‌های پیامکی">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field
                label="سرویس پیش‌فرض"
            >
                <x-admin.form.select
                    name="default_sms_provider"
                    placeholder="انتخاب سرویس"
                    :options="[
                        \App\Infrastructure\Services\Sms\Providers\SmsIrProvider::class => 'اس ام اس دات آی آر (SMS.ir)',
                        \App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider::class => 'ملی پیامک',
                        \App\Infrastructure\Services\Sms\Providers\FarazSmsProvider::class => 'فراز اس ام اس',
                    ]"
                    value="{{ get_setting('general', 'default_sms_provider') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات sms.ir">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-3">
            <x-admin.field
                label="کلید API"
            >
                <x-admin.form.input
                    name="smsir_api_key"
                    value="{{ get_setting('general', 'smsir_api_key') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="شناسه الگوی OTP"
            >
                <x-admin.form.input
                    name="smsir_otp_template_id"
                    value="{{ get_setting('general', 'smsir_otp_template_id') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="نام متغیر کد در الگوی OTP"
            >
                <x-admin.form.input
                    name="smsir_otp_parameter_name"
                    value="{{ get_setting('general', 'smsir_otp_parameter_name') }}"
                />
            </x-admin.field>

            @include('admin.system.settings.partials.general.login-alert-sms', ['provider' => 'smsir'])
            @include('admin.system.settings.partials.general.customer-notification-sms', ['provider' => 'smsir'])
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات فراز اس ام اس">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-3">
            <x-admin.field
                label="کلید API"
            >
                <x-admin.form.input
                    name="farazsms_api_key"
                    value="{{ get_setting('general', 'farazsms_api_key') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="شماره خط ارسال کننده"
            >
                <x-admin.form.input
                    name="farazsms_line_number"
                    value="{{ get_setting('general', 'farazsms_line_number') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="شناسه الگوی OTP"
            >
                <x-admin.form.input
                    name="farazsms_otp_pattern"
                    value="{{ get_setting('general', 'farazsms_otp_pattern') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="نام متغیر کد در الگوی OTP"
            >
                <x-admin.form.input
                    name="farazsms_otp_attribute_name"
                    value="{{ get_setting('general', 'farazsms_otp_attribute_name') }}"
                />
            </x-admin.field>

            @include('admin.system.settings.partials.general.login-alert-sms', ['provider' => 'farazsms'])
            @include('admin.system.settings.partials.general.customer-notification-sms', ['provider' => 'farazsms'])
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات ملی پیامک">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-3">
            <x-admin.field
                label="نام کاربری"
            >
                <x-admin.form.input
                    name="melipayamak_username"
                    value="{{ get_setting('general', 'melipayamak_username') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="رمزعبور"
            >
                <x-admin.form.input
                    name="melipayamak_password"
                    value="{{ get_setting('general', 'melipayamak_password') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="شناسه الگوی OTP"
            >
                <x-admin.form.input
                    name="melipayamak_otp_pattern"
                    value="{{ get_setting('general', 'melipayamak_otp_pattern') }}"
                />
            </x-admin.field>

            @include('admin.system.settings.partials.general.login-alert-sms', ['provider' => 'melipayamak'])
            @include('admin.system.settings.partials.general.customer-notification-sms', ['provider' => 'melipayamak'])
        </div>
    </x-admin.metabox>
</div>
