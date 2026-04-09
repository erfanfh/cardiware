@props([
    'title' => '',
    'subtitle' => '',
    'iconName' => null,
])

<x-front.cart>
    <x-front.cart-title
        :title="$title"
        :subtitle="$subtitle"
        :iconName="$iconName"
    />

    <div class="mt-3">
        {{ $slot }}
    </div>
</x-front.cart>
