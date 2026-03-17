@props([
   'name',
   'options' => [],
   'value' => null,
   'placeholder' => null,
])

<select
    name="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none focus:outline-none transition cursor-pointer'
    ]) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach($options as $key => $option)

        @php
            if(is_array($option)){
                $optValue = $option['value'];
                $optLabel = $option['label'];
            }else{
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
