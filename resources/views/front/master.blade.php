<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'مودینگ وی')</title>
    <x-favicons/>
    @vite(['resources/css/app.css', 'resources/css/front/main.css'])
    @stack('styles')
    @yield('head')
    @livewireStyles
</head>

<body class="bg-slate-50 text-slate-900">
@include('front.layouts.header')
@yield('content')
@include('front.layouts.footer')

@vite(['resources/js/front/main.js'])
@stack('scripts')
@livewireScripts
</body>

</html>
