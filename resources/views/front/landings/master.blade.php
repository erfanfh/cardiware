@php($locale = $locale ?? 'fa')
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A0F0D">

    <title>{{ $data['seo']['title'] ?? $data['product_name'] }}</title>
    <meta name="description" content="{{ $data['seo']['description'] ?? '' }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Moddingway">
    <meta property="og:locale" content="{{ $locale === 'en' ? 'en_US' : 'fa_IR' }}">
    <meta property="og:title" content="{{ $data['seo']['title'] ?? $data['product_name'] }}">
    <meta property="og:description" content="{{ $data['seo']['description'] ?? '' }}">
    <meta property="og:image" content="{{ asset('images/landings/og-default.svg') }}">

    @vite(['resources/css/front/landing.css', 'resources/js/front/landing.js'])

    {{-- override رنگ‌های تم (اختیاری: فایل storage/app/landing-themes.json) --}}
    @php($themeOvr = \App\Support\Landings::themeOverrides($data['theme'] ?? ''))
    @if(!empty($themeOvr['accent']) || !empty($themeOvr['bg']) || !empty($themeOvr['surface']))
        <style>
            .landing--{{ $data['theme'] }} {
                @if(!empty($themeOvr['accent']))--landing-accent: {{ $themeOvr['accent'] }}; --landing-glow: {{ $themeOvr['accent'] }}26;@endif
                @if(!empty($themeOvr['accent_2']))--landing-accent-2: {{ $themeOvr['accent_2'] }};@endif
                @if(!empty($themeOvr['accent_3']))--landing-accent-3: {{ $themeOvr['accent_3'] }};@endif
                @if(!empty($themeOvr['bg']))--landing-bg: {{ $themeOvr['bg'] }};@endif
                @if(!empty($themeOvr['surface']))--landing-surface: {{ $themeOvr['surface'] }};@endif
            }
        </style>
    @endif
</head>
<body class="landing landing--{{ $data['theme'] }} font-landing {{ $locale === 'en' ? 'font-landing-ltr' : '' }} antialiased">
    @yield('body')
</body>
</html>
