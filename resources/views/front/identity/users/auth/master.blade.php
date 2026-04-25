<!doctype html>
<html lang="fa" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {!! seo_head() !!}
    <x-favicons/>
    @vite('resources/css/app.css')
    @yield('head')
    @livewireStyles
</head>
<body dir="rtl" class="pb-10 md:py-0 bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
@yield('content')
@yield('scripts')
@vite(['resources/js/front/main.js'])
@livewireScripts
</body>
</html>
