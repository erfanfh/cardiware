@props([
    'align' => 'right',
    'width' => null,
])

<th
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->merge([
        'class' => 'whitespace-nowrap px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide'
    ]) }}
>
    <div @class([
        'text-right' => $align === 'right',
        'text-center' => $align === 'center',
        'text-left' => $align === 'left',
    ])>
        {{ $slot }}
    </div>
</th>
