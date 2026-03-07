@props([
    'columns' => 1
])

<div class="grid grid-cols-12 gap-5">
    @if($columns == 2)
        <div class="col-span-12 lg:col-span-9 space-y-5">
            {{ $slot }}
        </div>

        <div class="col-span-12 lg:col-span-3 space-y-5">
            {{ $sidebar ?? '' }}
        </div>
    @else
        <div class="col-span-12 space-y-5">
            {{ $slot }}
        </div>
    @endif
</div>
