@props([
    'text' => null,
])

@if($text)
    <div {{ $attributes->merge([
        'class' => 'rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-soft'
    ]) }}>
        {{ $text }}
    </div>
@endif
