@php
    /** @var App\Infrastructure\Services\Setting\Groups\IntegrationsSettings $integrations */
    $integrations = app(\App\Infrastructure\Services\Setting\Groups\IntegrationsSettings::class);
@endphp

{{-- جستجوی ترب: اسکریپت رسمی ویجت را از مستندات torob.com اینجا قرار دهید --}}
{{-- (data-website-id آماده است؛ فقط تگ اسکریپت رسمی را جایگزین کامنت کنید) --}}
@if($integrations->torobSearchEnabled())
    {{-- INTEGRATION POINT: Torob official search widget script goes here.
         Website ID: {{ $integrations->get('torob_search_website_id') }} --}}
@endif

{{-- ترب‌پی / اسنپ‌پی: پس از ثبت کلید، درگاه را طبق الگوی موجود در app/Domain/Payment پیاده کنید --}}
{{-- و گزینه پرداخت را در تسویه نمایش دهید (تا آن زمان هیچ تغییری در فرایند پرداخت نیست) --}}
