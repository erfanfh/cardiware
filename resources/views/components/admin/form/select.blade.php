@props([
   'name' => null,
   'options' => [],
   'value' => null,
   'placeholder' => null,
])

<select
    name="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300'
    ]) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach($options as $key => $option)
        @php
            if (is_array($option)) {
                $optValue = $option['value'];
                $optLabel = $option['label'];
            } else {
                $optValue = $key;
                $optLabel = $option;
            }
        @endphp

        <option
            value="{{ $optValue }}"
            @selected($value == $optValue)
        >
            {{ $optLabel }}
        </option>
    @endforeach
</select>
