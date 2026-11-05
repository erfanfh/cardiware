@extends('admin.layout')

@section('title', 'یکپارچه‌سازی‌های بازارگاه')

@section('content')
    <x-admin.page title="یکپارچه‌سازی‌های بازارگاه"
                  subtitle="ترب‌پی، اسنپ‌پی و جستجوی ترب — تا زمانی که کلید معتبر ثبت نشود چیزی در سایت لود نمی‌شود.">

        <x-admin.form action="{{ route('admin.settings.integrations.store') }}" method="put">
            <x-admin.metabox title="ترب‌پی (Torob Pay)">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field label="فعال باشد">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="hidden" name="torob_pay_enabled" value="0">
                            <input type="checkbox" name="torob_pay_enabled" value="1"
                                   @checked(old('torob_pay_enabled', (bool) get_setting('integrations', 'torob_pay_enabled', false)))
                                   class="h-4 w-4 rounded border-slate-300 text-brand-600">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">نمایش گزینه پرداخت ترب‌پی در تسویه</span>
                        </label>
                    </x-admin.field>

                    <x-admin.field label="کلید پذیرندگی (Merchant Key)"
                                   hint="پس از عقد قرارداد از پنل ترب دریافت می‌شود.">
                        <x-admin.form.input name="torob_pay_merchant_key" dir="ltr" type="password"
                                            :value="old('torob_pay_merchant_key', get_setting('integrations', 'torob_pay_merchant_key'))"/>
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="اسنپ‌پی (Snapp Pay)">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field label="فعال باشد">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="hidden" name="snapp_pay_enabled" value="0">
                            <input type="checkbox" name="snapp_pay_enabled" value="1"
                                   @checked(old('snapp_pay_enabled', (bool) get_setting('integrations', 'snapp_pay_enabled', false)))
                                   class="h-4 w-4 rounded border-slate-300 text-brand-600">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">نمایش گزینه پرداخت اسنپ‌پی (اعتباری) در تسویه</span>
                        </label>
                    </x-admin.field>

                    <x-admin.field label="کلید API اسنپ‌پی"
                                   hint="از پنل توسعه‌دهندگان اسنپ‌پی دریافت می‌شود.">
                        <x-admin.form.input name="snapp_pay_api_key" dir="ltr" type="password"
                                            :value="old('snapp_pay_api_key', get_setting('integrations', 'snapp_pay_api_key'))"/>
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="جستجوی ترب (Torob Search)">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-admin.field label="فعال باشد">
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <input type="hidden" name="torob_search_enabled" value="0">
                            <input type="checkbox" name="torob_search_enabled" value="1"
                                   @checked(old('torob_search_enabled', (bool) get_setting('integrations', 'torob_search_enabled', false)))
                                   class="h-4 w-4 rounded border-slate-300 text-brand-600">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">جعبه جستجوی ترب در هدر سایت</span>
                        </label>
                    </x-admin.field>

                    <x-admin.field label="شناسه سایت در ترب (Website ID)"
                                   hint="طبق مستندات official-search ترب (torob.com) از پنل همکاری دریافت می‌شود.">
                        <x-admin.form.input name="torob_search_website_id" dir="ltr"
                                            :value="old('torob_search_website_id', get_setting('integrations', 'torob_search_website_id'))"/>
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.form.button type="submit" color="brand">ذخیره یکپارچه‌سازی‌ها</x-admin.form.button>
        </x-admin.form>
    </x-admin.page>
@endsection
