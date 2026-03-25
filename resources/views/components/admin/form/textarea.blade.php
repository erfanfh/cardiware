@props([
    'name' => null,
    'rows' => 4,
    'value' => '',
])

<textarea
    rows="{{ $rows }}"
    @if($name) name="{{ $name }}" @endif
    {{ $attributes->merge([
        'class' => 'w-full min-h-[100px] rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm leading-7 text-slate-800 placeholder:text-slate-400 outline-none resize-y transition focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300'
    ]) }}
>{{ $value }}</textarea>
