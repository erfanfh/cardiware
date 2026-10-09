@props([
    'active' => false,
    'clickable' => false,
    'tone' => 'default',
])

<tr
    {{ $attributes->merge([
        'class' => collect([
            'group transition',
            $clickable ? 'cursor-pointer' : '',
            $tone === 'danger' ? '!bg-rose-50 hover:!bg-rose-100/70' : ($active ? 'bg-brand-50/40' : 'bg-white'),
            $tone === 'danger' ? '' : 'hover:bg-slate-50/80',
        ])->filter()->implode(' ')
    ]) }}
>
    {{ $slot }}
</tr>
