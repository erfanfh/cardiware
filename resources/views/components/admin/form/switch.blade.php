@props([
    'name' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    $rootAttributes = $attributes
        ->except(['x-bind:name', 'name'])
        ->merge([
            'class' => 'inline-flex items-center',
        ]);

    $nameAttributes = $attributes->only(['x-bind:name']);
@endphp

<label
    x-data="{ on: @js((bool) $checked) }"
    x-modelable="on"
    {{ $rootAttributes }}
>
    <input
        type="hidden"
        @if($name) name="{{ $name }}" @endif
        {{ $nameAttributes }}
        :value="on ? 1 : 0"
        {{ $disabled ? 'disabled' : '' }}
    >

    <button
        type="button"
        role="switch"
        :aria-checked="on.toString()"
        @if(!$disabled)
            @click="on = !on"
        @endif
        @class([
            'relative inline-flex h-7 w-12 shrink-0 items-center rounded-full border transition-all duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2',
            'cursor-pointer' => !$disabled,
            'cursor-not-allowed opacity-60' => $disabled,
        ])
        :class="on
            ? 'bg-emerald-500 border-emerald-500 focus:ring-emerald-200'
            : 'bg-slate-200 border-slate-300 focus:ring-slate-300'"
        {{ $disabled ? 'disabled' : '' }}
    >
        <span
            class="pointer-events-none absolute top-1/2 h-5 w-5 -translate-y-1/2 rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,0.18),0_1px_2px_rgba(0,0,0,0.10)] ring-1 ring-slate-200 transition-all duration-200 ease-out"
            :class="on ? 'right-1' : 'left-1'"
        ></span>
    </button>
</label>
