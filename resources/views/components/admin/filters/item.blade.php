@props([
    'label' => null,
    'hint' => null,
    'span' => 'col-span-12 md:col-span-6 xl:col-span-3',
])

<x-admin.field :label="$label" :hint="$hint" {{ $attributes->merge(['class' => $span]) }}>
    {{ $slot }}
</x-admin.field>
