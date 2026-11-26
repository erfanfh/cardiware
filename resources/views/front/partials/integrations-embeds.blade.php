@php
    /** @var App\Infrastructure\Services\Setting\Groups\IntegrationsSettings $integrations */
    $integrations = app(\App\Infrastructure\Services\Setting\Groups\IntegrationsSettings::class);
@endphp

{{-- Torob search: official widget script from docs torob.com insert here --}}
{{-- (data-website-id is ready; replace the comment with official script) --}}
@if($integrations->torobSearchEnabled())
    {{-- INTEGRATION POINT: Torob official search widget script goes here.
         Website ID: {{ $integrations->get('torob_search_website_id') }} --}}
@endif

{{-- TorobPay / SnappPay: after registering the key, use the existing pattern in app/Domain/Payment implement --}}
{{-- and display payment option at checkout (no payment flow change until then) --}}
