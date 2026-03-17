@props([
   'name',
   'options' => [],
   'value' => null,
   'placeholder' => null,
])

<select
    name="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'w-full rounded-[10px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 py-[9px] px-[11px] text-[0.88rem] text-[var(--text-main)] dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-800 outline-none transition-colors focus-visible:outline focus-visible:outline-red-400 focus-visible:border focus-visible:border-red-400'
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
