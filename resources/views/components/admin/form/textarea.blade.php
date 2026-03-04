@props([
    'name' => null,
    'rows' => 4,
])

<textarea
    rows="{{ $rows }}"
    @if($name) name="{{ $name }}" @endif
    {{ $attributes->merge([
        'class' => 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:ring-2 focus:ring-orange-200 focus:border-orange-300 placeholder:text-slate-400'
    ]) }}
>{{ $slot }}</textarea>
