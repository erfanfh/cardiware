@props([
    'stretch' => false,
])

<div {{ $attributes->merge([
    'class' => ($stretch ? 'flex-1 ' : '') . 'flex flex-wrap items-center gap-3'
]) }}>
    {{ $slot }}
</div>
