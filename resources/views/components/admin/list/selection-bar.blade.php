<div
    x-cloak
    x-show="selectedCount > 0"
    x-transition.opacity
    {{ $attributes->merge([
        'class' => 'flex flex-col gap-3 rounded-2xl border border-brand-200 bg-brand-50/70 p-4 sm:flex-row sm:items-center sm:justify-between'
    ]) }}
>
    <div class="flex items-center gap-2 text-sm text-slate-700">
        <span
            class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-white px-2 text-xs font-bold text-brand-700 ring-1 ring-brand-200">
            <span x-text="selectedCount"></span>
        </span>

        <span class="font-medium">مورد انتخاب شده است</span>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        {{ $slot }}
    </div>
</div>
