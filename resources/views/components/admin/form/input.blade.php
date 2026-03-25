@props([
    'name' => null,
    'type' => 'text',
    'value' => '',
])

<input
    type="{{ $type }}"
    value="{{ $value }}"
    @if($name) name="{{ $name }}" @endif
    {{ $attributes->merge([
        'class' => 'w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 placeholder:text-slate-400 outline-none transition focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300'
    ]) }}
/>
