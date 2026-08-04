@props([
    'badge',
])

<span
    title="{{ $badge->description() }}"
    aria-label="{{ $badge->description() }}"
    @class([
        'inline-flex h-5 shrink-0 items-center gap-1 rounded-lg border px-1.5 text-[10px] font-black leading-none',

        'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300'
            => $badge->value === 'admin',

        'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300'
            => $badge->value === 'post_author',

        'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300'
            => $badge->value === 'product_seller',

        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
            => $badge->value === 'verified_buyer',
    ])
>
    <span
        @class([
            'h-1.5 w-1.5 shrink-0 rounded-full',

            'bg-violet-500 dark:bg-violet-400'
                => $badge->value === 'admin',

            'bg-sky-500 dark:bg-sky-400'
                => $badge->value === 'post_author',

            'bg-indigo-500 dark:bg-indigo-400'
                => $badge->value === 'product_seller',

            'bg-emerald-500 dark:bg-emerald-400'
                => $badge->value === 'verified_buyer',
        ])
    ></span>

    <span>{{ $badge->label() }}</span>
</span>
