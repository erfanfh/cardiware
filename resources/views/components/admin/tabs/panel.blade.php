@props([
   'name',
])

<div x-show="activeTab === @js($name)" x-cloak>
    {{ $slot }}
</div>
