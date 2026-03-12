@props([
    'name' => null,
    'default' => null,
    'value' => null
])

<div class="color-picker-wrapper"
     data-name="{{ $name }}"
     @if($default) data-default="{{ $default }}" @endif
     @if($value) data-value="{{ $value }}" @endif>
</div>
