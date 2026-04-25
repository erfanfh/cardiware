<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
@yield('content')
@include('front.partials.admin-bar')
@include('front.footer.index')
<x-front.lightbox-gallery/>
@vite(['resources/js/front/main.js'])
@stack('scripts')
@livewireScripts
</body>

</html>
