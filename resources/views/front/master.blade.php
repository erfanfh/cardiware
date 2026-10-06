<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(request()->routeIs('preview.*'))
        <title>پیشنمایش</title>
        <meta name="robots" content="noindex,nofollow,noarchive">
    @endif
    {!! seo_head() !!}
    <x-favicons/>
    @include('front.scripts.dark-light')
    @vite(['resources/css/app.css', 'resources/css/front/main.css'])
    @stack('styles')
    @yield('head')
    @livewireStyles
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
@include('front.header.index')
@if(request()->routeIs('preview.*'))
    <div
        class="sticky top-0 z-10 border-y border-amber-300 bg-amber-100 px-4 py-2 text-center text-xs font-black text-amber-950 shadow-sm">
        حالت پیش‌نمایش مدیریتی — این محتوا در مسیر عمومی سایت قابل مشاهده نیست.
    </div>
@endif
@yield('content')
@include('front.partials.admin-bar')
@include('front.footer.index')
<x-front.lightbox-gallery/>
<x-front.scroll-to-top/>
<x-front.registration-gift-toast/>
<x-front.loyalty-earned-toast/>
<livewire:front.promotion.floating-coupon-box/>
@unless(request()->routeIs('preview.*'))
    @php
        $popupTargetType = isset($post) ? 'post' : (isset($page) ? 'page' : (isset($product) ? 'product' : (request()->routeIs('home') ? 'home' : 'other')));
        $popupTargetId = isset($post) ? $post->getKey() : (isset($page) ? $page->getKey() : (isset($product) ? $product->getKey() : null));
    @endphp
    @include('front.notification.popups', [
        'popups' => app(\App\Application\Notification\GetVisiblePopupsAction::class)
            ->execute($popupTargetType, $popupTargetId, auth()->user()),
    ])
@endunless
@vite(['resources/js/front/main.js'])
@stack('scripts')
@livewireScripts
</body>

</html>
