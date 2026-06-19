<div class="flex flex-col gap-5">
    <div class="flex justify-end">
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors [&_svg]:w-3 [&_svg]:h-3">
            @include('icons.arrow-left-01')
            <span>بازگشت به خانه</span>
        </a>
    </div>

    <div class="text-center">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">
            @yield('auth-title')
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            @yield('auth-description')
        </p>
    </div>

    @yield('auth-form')
</div>
