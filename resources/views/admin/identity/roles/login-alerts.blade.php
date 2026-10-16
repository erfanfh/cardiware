@extends('admin.layout')

@section('title', 'اعلان ورود نقش ' . $role->title)

@section('content')
    <x-admin.form :action="route('admin.roles.login-alerts.update', $role)" method="put">
        <x-admin.page>
            <x-admin.form.validation-errors/>
            <x-admin.metabox :title="'اعلان ورود: ' . $role->title">
                <p class="mb-6 text-sm leading-7 text-slate-600">
                    پس از هر ورود موفق اعضای این نقش به سایت یا لانچر، از طریق روش‌های فعال به خود کاربر اطلاع داده می‌شود.
                    بازیابی خودکار نشست اعلان جدیدی ندارد. هر دو روش می‌توانند هم‌زمان فعال باشند.
                </p>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <x-admin.field label="ارسال ایمیل اعلان ورود">
                        <x-admin.form.switch name="login_email_enabled" :checked="old('login_email_enabled', $role->login_email_enabled)"/>
                        <p class="mt-2 text-xs leading-6 text-slate-500">به ایمیل ثبت‌شدهٔ کاربر ارسال می‌شود. حساب بدون ایمیل معتبر از این روش عبور می‌کند.</p>
                    </x-admin.field>
                    <x-admin.field label="ارسال پیامک اعلان ورود">
                        <x-admin.form.switch name="login_sms_enabled" :checked="old('login_sms_enabled', $role->login_sms_enabled)"/>
                        <p class="mt-2 text-xs leading-6 text-slate-500">به شمارهٔ موبایل کاربر و با الگوی اعلان ورود سرویس پیامکی پیش‌فرض ارسال می‌شود.</p>
                    </x-admin.field>
                </div>
                @can('setting.general')
                    <a class="mt-6 inline-block text-sm text-brand-600" href="{{ route('admin.settings.general', ['tab' => 'sms-panel']) }}">تنظیم سرویس پیامک و الگوهای اعلان ورود</a>
                @endcan
                <p class="mt-4 text-xs leading-6 text-slate-500">زمان، IP و محل ورود در پیام درج می‌شوند. ارسال‌ها در پس‌زمینه انجام می‌شوند؛ سرویس ایمیل سرور و پردازشگر صف باید فعال باشند.</p>
            </x-admin.metabox>
        </x-admin.page>
        <x-admin.form.save-button label="ذخیره تنظیمات اعلان ورود"/>
    </x-admin.form>
@endsection
