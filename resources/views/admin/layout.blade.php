<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'داشبورد')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/jalalidatepicker.min.css') }}">
    <script src="{{ asset('js/jalalidatepicker.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/admin/admin.css', 'resources/js/admin/admin.js'])

    @stack('styles')
</head>
<body>

<div class="flex min-h-[100vh] bg-[var(--bg-body)]">
    <div class="fixed inset-0 bg-[rgba(15,23,42,0.45)] opacity-0 pointer-events-none z-20 transition-all" id="SidebarBackdrop"></div>

    @include('admin.partials.sidebar')

    <div class="flex-1 min-w-0 flex flex-col">

        @include('admin.partials.topbar')

        <main class="flex-1 pt-[18px] px-[20px] pb-[24px] min-h-0 overflow-y-auto">
            @yield('page_header')
            @yield('content')
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
</body>
</html>
