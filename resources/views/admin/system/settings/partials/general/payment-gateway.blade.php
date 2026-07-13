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
</div>
