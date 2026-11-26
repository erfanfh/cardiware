<!doctype html>
<html lang="en" dir="ltr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {!! seo_head() !!}
    <x-favicons/>
    @include('front.scripts.dark-light')
    @vite(['resources/css/app.css', 'resources/css/front/main.css'])
    @yield('head')
    @stack('styles')
    @livewireStyles
</head>
<body dir="ltr" class="bg-slate-950 text-slate-100">
<h1 class="sr-only">Sign in / Register at {{ get_setting('general', 'title') }}</h1>
@yield('content')
@yield('scripts')
@stack('scripts')
@vite(['resources/js/front/main.js'])
@livewireScripts
</body>
</html>
