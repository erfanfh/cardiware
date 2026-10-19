@extends('admin.layout')

@section('title', 'تنظیمات اعلان‌ها')

@section('content')
    <x-admin.page>
        <x-admin.form.validation-errors/>
        <x-admin.form :action="route('admin.settings.notifications.recent-purchases.update')" method="put">
            <x-admin.metabox title="اعلان خریدهای اخیر">
                <x-admin.field label="نمایش خریدهای اخیر در سایت">
                    <x-admin.form.switch name="recent_purchases_enabled" :checked="old('recent_purchases_enabled', $recentPurchasesEnabled)"/>
                    <p class="mt-2 text-xs leading-6 text-slate-500">نام و نام خانوادگی خریدار و محصول خریداری‌شده در یک اعلان کوچک پایین صفحات عمومی نمایش داده می‌شود. این گزینه به‌صورت پیش‌فرض فعال است.</p>
                </x-admin.field>
                <div class="mt-5">
                    <x-admin.form.button type="submit">ذخیره تنظیمات خریدهای اخیر</x-admin.form.button>
                </div>
            </x-admin.metabox>
        </x-admin.form>
        <x-admin.form :action="route('admin.settings.notifications.sms.update')" method="put">
            <x-admin.metabox title="اعلان‌های پیامکی کاربران">
                <p class="mb-5 text-sm leading-7 text-slate-500">هر دو اعلان به‌صورت پیش‌فرض غیرفعال هستند. پیامک به شمارهٔ موبایل صاحب تیکت یا خریدار ارسال می‌شود.</p>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @foreach($smsTypes as $type)
                        <x-admin.field :label="$type->label()">
                            <x-admin.form.switch :name="$type->settingKey()" :checked="old($type->settingKey(), $smsSettings->get($type->settingKey(), false))"/>
                            <p class="mt-2 text-xs leading-6 text-slate-500">
                                @if($type === \App\Domain\Notification\Enums\CustomerSmsType::TICKET_REPLY)
                                    پس از ثبت هر پاسخ پشتیبانی، صاحب تیکت با یک پیامک حاوی شمارهٔ تیکت مطلع می‌شود.
                                @else
                                    پس از پرداخت موفق، یک پیامک حاوی شمارهٔ سفارش ارسال می‌شود؛ حتی اگر سفارش چند محصول داشته باشد.
                                @endif
                            </p>
                        </x-admin.field>
                    @endforeach
                </div>
                <a href="{{ route('admin.settings.general', ['tab' => 'sms-panel']) }}" class="mt-5 inline-block text-sm text-brand-600">تنظیم سرویس پیامک و الگوهای پاسخ تیکت و خرید موفق</a>
                <div class="mt-5">
                    <x-admin.form.button type="submit">ذخیره تنظیمات پیامک</x-admin.form.button>
                </div>
            </x-admin.metabox>
        </x-admin.form>
        <x-admin.metabox title="اعلان‌های داخل سیستم">
            <p class="mb-5 text-sm text-slate-500">هر رویداد را جداگانه فعال یا غیرفعال کنید. این تغییر فقط روی اعلان‌های بعدی اثر می‌گذارد.</p>
            <div class="space-y-3">
                @foreach($events as $key => $label)
                    @php($enabled = !isset($settings[$key]) || (bool) $settings[$key])
                    <form method="post" action="{{ route('admin.settings.notifications.update') }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="event_key" value="{{ $key }}">
                        <div>
                            <div class="font-bold">{{ $label }}</div>
                            <div class="mt-1 text-xs text-slate-500" dir="ltr">{{ $key }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <select name="is_enabled" aria-label="وضعیت {{ $label }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="1" @selected($enabled)>فعال</option>
                                <option value="0" @selected(!$enabled)>غیرفعال</option>
                            </select>
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-bold text-white">ذخیره</button>
                        </div>
                    </form>
                @endforeach
            </div>
        </x-admin.metabox>
    </x-admin.page>
@endsection
