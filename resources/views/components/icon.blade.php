@props([
    'name' => null,
    'svg' => null,
    'fallback' => null,
])

@php
    $resolvedSvg = filled($svg)
        ? $svg
        : app(\App\Application\Icon\Actions\ResolveIconSvgAction::class)->execute($name);
@endphp

@if($resolvedSvg)
    <span
        {{ $attributes->merge([
            'class' => 'inline-flex shrink-0 items-center justify-center leading-none [&_svg]:h-full [&_svg]:w-full'
        ]) }}
        aria-hidden="true"
    >
        {!! $resolvedSvg !!}
    </span>
@elseif(filled($fallback))
    <span {{ $attributes }}>{{ $fallback }}</span>
@endif
