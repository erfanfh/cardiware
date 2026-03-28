@props([
    'open' => false,
])

<div
    x-data="{ open: @js($open) }"
    {{ $attributes->merge(['class' => 'space-y-4']) }}
>
    {{ $slot }}
</div>
