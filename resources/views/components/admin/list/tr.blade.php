@props([
    'active' => false,
    'clickable' => false,
])

<tr
    {{ $attributes->merge([
        'class' => collect([
            'group transition',
            $clickable ? 'cursor-pointer' : '',
            $active ? 'bg-brand-50/40' : 'bg-white',
            'hover:bg-slate-50/80',
        ])->filter()->implode(' ')
    ]) }}
>
    {{ $slot }}
</tr>
