@props([
    'name' => 'search',
    'value' => '',
    'placeholder' => 'جستجو...',
])

<div class="relative min-w-[220px] flex-1 max-w-md">
    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="m21 21-4.35-4.35" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="11" cy="11" r="6" stroke-width="1.8"/>
        </svg>
    </span>

    <input
        type="text"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl border border-slate-200 bg-white py-2 pr-9 pl-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-2 focus:ring-brand-100'
        ]) }}
    />
</div>
