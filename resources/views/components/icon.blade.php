@props([
    'name' => null,
    'fallback' => null,
])

@php
    $svg = app(\App\Application\Icon\Actions\ResolveIconSvgAction::class)->execute($name);
@endphp

@if($svg)
    <span
        {{ $attributes->merge([
            'class' => 'inline-flex shrink-0 items-center justify-center leading-none [&_svg]:h-full [&_svg]:w-full'
        ]) }}
        aria-hidden="true"
    >
        {!! $svg !!}
    </span>
@elseif(filled($fallback))
    <span {{ $attributes }}>{{ $fallback }}</span>
@endif
