@props([
    'navClass' => 'mb-5 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400',
    'linkClass' => 'font-bold transition hover:text-brand-700 dark:hover:text-brand-300',
    'currentClass' => 'font-semibold text-slate-700 dark:text-slate-200',
    'separatorClass' => '',
    'compactPenultimateOnMobile' => false,
    'fallbackItems' => [],
])

@php
    $breadcrumbItems = app(\App\Application\Seo\Support\BreadcrumbTrail::class)->items();
    if (count($breadcrumbItems) < 2) {
        $breadcrumbItems = $fallbackItems;
    }
@endphp

@if(count($breadcrumbItems) > 1)
    <nav aria-label="مسیر صفحه" {{ $attributes->class([$navClass]) }}>
        @foreach($breadcrumbItems as $breadcrumbItem)
            @php
                $hideOnMobile = $compactPenultimateOnMobile
                    && count($breadcrumbItems) > 3
                    && $loop->index === count($breadcrumbItems) - 2;
            @endphp
            @unless($loop->first)
                <span aria-hidden="true" class="{{ $hideOnMobile ? 'hidden sm:inline-flex' : 'inline-flex' }} items-center justify-center [&_svg]:h-3 [&_svg]:w-3 {{ $separatorClass }}">
                    @include('icons.arrow-left-01')
                </span>
            @endunless

            @if(!$loop->last && filled($breadcrumbItem['url'] ?? null))
                <a href="{{ $breadcrumbItem['url'] }}" class="{{ $linkClass }} {{ $hideOnMobile ? 'hidden sm:inline' : '' }}">{{ $breadcrumbItem['name'] }}</a>
            @else
                <span @if($loop->last) aria-current="page" @endif class="{{ $currentClass }}">{{ $breadcrumbItem['name'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
