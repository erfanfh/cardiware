@props([
    'align' => 'right',
    'muted' => false,
])

<td
    {{ $attributes->merge([
        'class' => collect([
            'px-4 py-4 align-middle',
            $muted ? 'text-slate-500' : 'text-slate-700',
        ])->implode(' ')
    ]) }}
>
    <div class="@class([
        'text-right' => $align === 'right',
        'text-center' => $align === 'center',
        'text-left' => $align === 'left',
    ])">
        {{ $slot }}
    </div>
</td>
