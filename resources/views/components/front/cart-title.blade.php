@props([
    'title',
    'subtitle' => null,
    'iconName' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 mb-5']) }}>
    @if($iconName)
        <span
            class="inline-flex h-9 w-9 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            @includeIf('icons.' . $iconName)
        </span>
    @endif

    <div>
        <h2 class="text-base font-extrabold">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-xs text-slate-500 mt-1">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</div>
