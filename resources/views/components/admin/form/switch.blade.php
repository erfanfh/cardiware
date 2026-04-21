@props([
    'name' => null,
    'checked' => false,
    'disabled' => false,
])

@php
    $normalizeToBool = static function (mixed $value): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(
                strtolower(trim($value)),
                ['1', 'true', 'on', 'yes'],
                true
            );
        }

        return false;
    };

    $isChecked = $normalizeToBool($checked);

    $rootAttributes = $attributes
        ->except(['x-bind:name', 'name'])
        ->merge([
            'class' => 'inline-flex items-center',
        ]);

    $nameAttributes = $attributes->only(['x-bind:name']);
@endphp

<label
    x-data="{
        on: @js($isChecked),
        normalizeBoolean(value) {
            if (typeof value === 'boolean') return value;
            if (typeof value === 'number') return value === 1;
            if (typeof value === 'string') {
                return ['1', 'true', 'on', 'yes'].includes(value.toLowerCase().trim());
            }
            return false;
        },
        isOn() {
            return this.normalizeBoolean(this.on);
        }
    }"
    x-modelable="on"
    x-init="on = normalizeBoolean(on)"
    {{ $rootAttributes }}
>
    <input
        type="hidden"
        @if($name) name="{{ $name }}" @endif
        {{ $nameAttributes }}
        :value="isOn() ? 1 : 0"
        {{ $disabled ? 'disabled' : '' }}
    >

    <button
        type="button"
        role="switch"
        :aria-checked="isOn() ? 'true' : 'false'"
        @if(!$disabled)
            @click="on = !isOn()"
        @endif
        @class([
            'relative inline-flex h-7 w-12 shrink-0 items-center rounded-full border transition-all duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2',
            'cursor-pointer' => !$disabled,
            'cursor-not-allowed opacity-60' => $disabled,
        ])
        :class="isOn()
            ? 'bg-emerald-500 border-emerald-500 focus:ring-emerald-200'
            : 'bg-slate-200 border-slate-300 focus:ring-slate-300'"
        {{ $disabled ? 'disabled' : '' }}
    >
        <span
            class="pointer-events-none absolute top-1/2 h-5 w-5 -translate-y-1/2 rounded-full bg-white shadow-[0_1px_3px_rgba(0,0,0,0.18),0_1px_2px_rgba(0,0,0,0.10)] ring-1 ring-slate-200 transition-all duration-200 ease-out"
            :class="isOn() ? 'right-1' : 'left-1'"
        ></span>
    </button>
</label>
