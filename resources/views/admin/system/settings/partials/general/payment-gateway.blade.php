@php
    use App\Domain\Financial\Enums\PaymentGatewayEnum;

    $gateways = collect(PaymentGatewayEnum::cases())
        ->filter(fn (PaymentGatewayEnum $gateway) => $gateway->isExternal())
        ->mapWithKeys(fn (PaymentGatewayEnum $gateway) => [
            $gateway->value => $gateway->label(),
        ])
        ->toArray();
@endphp

<div class="space-y-6">
    <x-admin.metabox title="پذیرش قوانین پیش از پرداخت">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field label="الزام پذیرش قوانین">
                <x-admin.form.select
                    name="checkout_terms_enabled"
                    :options="[0 => 'غیرفعال', 1 => 'فعال']"
                    value="{{ old('checkout_terms_enabled', (int) get_setting('general', 'checkout_terms_enabled', false)) }}"
                />
            </x-admin.field>

            <x-admin.field label="لینک صفحه قوانین">
                <x-admin.form.input
                    name="checkout_terms_url"
                    type="text"
                    dir="ltr"
                    :value="old('checkout_terms_url', get_setting('general', 'checkout_terms_url'))"
                />

                @error('checkout_terms_url')
                <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                @enderror
            </x-admin.field>
        </div>

        <p class="mt-4 text-xs leading-6 text-slate-500">
            با فعال‌سازی این گزینه، کاربر تا زمانی که پذیرش قوانین را در سبد خرید تأیید نکند نمی‌تواند وارد مرحله پرداخت
            شود.
        </p>
    </x-admin.metabox>

    <x-admin.metabox title="مالیات سفارش‌ها">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field label="وضعیت مالیات">
                <x-admin.form.select
                    name="tax_enabled"
                    :options="[0 => 'غیرفعال', 1 => 'فعال']"
                    value="{{ old('tax_enabled', (int) get_setting('general', 'tax_enabled', false)) }}"
                />
            </x-admin.field>

            <x-admin.field label="درصد مالیات">
                <x-admin.form.input
                    name="tax_rate_percent"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    dir="ltr"
                    :value="old('tax_rate_percent', get_setting('general', 'tax_rate_percent', 0))"
                />
            </x-admin.field>
        </div>

        <p class="mt-4 text-xs leading-6 text-slate-500">
            مالیات روی مبلغ خالص سفارش پس از کسر همه تخفیف‌ها محاسبه می‌شود. تغییر این تنظیم روی سفارش‌های ثبت‌شده قبلی
            اثری ندارد.
        </p>
    </x-admin.metabox>

    <x-admin.metabox title="درگاه‌های پرداخت">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field
                label="سرویس پیش‌فرض"
            >
                <x-admin.form.select
                    name="default_payment_gateway"
                    placeholder="انتخاب سرویس"
                    :options="$gateways"
                    value="{{ get_setting('general', 'default_payment_gateway') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات زیبال">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <x-admin.field
                label="حالت محیط تست"
            >
                <x-admin.form.select
                    name="zibal_sandbox"
                    :options="[
                        0 => 'خیر',
                        1 => 'بله',
                    ]"
                    value="{{ get_setting('general', 'zibal_sandbox') }}"
                />
            </x-admin.field>

            <x-admin.field
                class="lg:col-span-3"
                label="مرچنت آی دی"
            >
                <x-admin.form.input
                    name="zibal_merchant_id"
                    value="{{ get_setting('general', 'zibal_merchant_id') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات زرین پال">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <x-admin.field
                label="حالت محیط تست"
            >
                <x-admin.form.select
                    name="zarinpal_sandbox"
                    :options="[
                        0 => 'خیر',
                        1 => 'بله',
                    ]"
                    value="{{ get_setting('general', 'zarinpal_sandbox') }}"
                />
            </x-admin.field>

            <x-admin.field
                class="lg:col-span-3"
                label="مرچنت آی دی"
            >
                <x-admin.form.input
                    name="zarinpal_merchant_id"
                    value="{{ get_setting('general', 'zarinpal_merchant_id') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات پی پینگ">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <x-admin.field
                label="حالت محیط تست"
            >
                <x-admin.form.select
                    name="payping_sandbox"
                    :options="[
                        0 => 'خیر',
                        1 => 'بله',
                    ]"
                    value="{{ get_setting('general', 'payping_sandbox') }}"
                />
            </x-admin.field>

            <x-admin.field
                class="lg:col-span-3"
                label="توکن"
            >
                <x-admin.form.input
                    name="payping_token"
                    value="{{ get_setting('general', 'payping_token') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="تنظیمات آقای پرداخت">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <x-admin.field
                label="حالت محیط تست"
            >
                <x-admin.form.select
                    name="aqayepardakht_sandbox"
                    :options="[
                        0 => 'خیر',
                        1 => 'بله',
                    ]"
                    value="{{ get_setting('general', 'aqayepardakht_sandbox') }}"
                />
            </x-admin.field>

            <x-admin.field
                class="lg:col-span-3"
                label="پین"
            >
                <x-admin.form.input
                    name="aqayepardakht_pin"
                    value="{{ get_setting('general', 'aqayepardakht_pin') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
