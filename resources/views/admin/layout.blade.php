<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'داشبورد')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/jalalidatepicker.min.css') }}">
    <script src="{{ asset('js/jalalidatepicker.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/admin/admin.css', 'resources/js/admin/admin.js'])

    @stack('styles')
    @livewireStyles
</head>
<body>

<div class="flex min-h-[100vh] bg-[var(--bg-body)] dark:bg-gray-900">
    <div
        class="fixed inset-0 bg-[rgba(15,23,42,0.45)] dark:bg-[rgba(0,0,0,0.6)] opacity-0 pointer-events-none z-20 transition-all"
        id="SidebarBackdrop"></div>

    @include('admin.partials.sidebar')

    <div class="flex-1 min-w-0 flex flex-col">

        @include('admin.partials.topbar')

        <main class="flex-1 pt-[18px] px-[20px] pb-[24px] min-h-0 overflow-y-auto space-y-4">
            @yield('page_header')
            @yield('content')

            @livewire('admin.media.media-picker', ['mode' => 'singleton'], key('singleton-media-picker'))
        </main>
    </div>

    <x-admin.flash-modal/>
</div>

<script>
    jalaliDatepicker.startWatch({
        time: true,
        hasSecond: false,
        showTodayBtn: true,
    });
</script>

@stack('scripts')
@livewireScripts
</body>
</html>
