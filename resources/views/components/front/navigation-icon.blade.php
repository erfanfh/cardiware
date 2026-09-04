@props([
    'item',
])

@php
    $svg = data_get($item, 'icon_svg');
    $legacyIcon = data_get($item, 'icon');
@endphp

@if(filled($svg))
    <span {{ $attributes->class('inline-grid shrink-0 place-items-center [&_svg]:block [&_svg]:max-h-full [&_svg]:max-w-full') }} aria-hidden="true">
        {!! $svg !!}
    </span>
@elseif(filled($legacyIcon) && !str_contains((string) $legacyIcon, ':'))
    <span {{ $attributes->class('inline-grid shrink-0 place-items-center') }} aria-hidden="true">
        {{ $legacyIcon }}
    </span>
@endif
