@props([
    'name',
    'id' => null,
    'label' => null,
    'checked' => false,
    'value' => 1,
    'uncheckedValue' => 0,
    'disabled' => false,
])

@php
    $id = $id ?? $name;
    $isChecked = old($name, null);
    if (is_null($isChecked)) {
        $isChecked = (bool) $checked;
    } else {
        $isChecked = (bool) $isChecked;
    }
@endphp

<label class="za-switch {{ $disabled ? 'is-disabled' : '' }}">
    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ $uncheckedValue }}"
    >

    <input
        type="checkbox"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($isChecked)
        @disabled($disabled)
    >

    <span class="za-switch__track">
        <span class="za-switch__thumb"></span>
    </span>

    @if($label)
        <span class="za-switch__label">{{ $label }}</span>
    @endif
</label>
