@props([
    'name' => 'search',
    'value' => '',
    'placeholder' => 'جستجو...',
])

<div class="relative min-w-[220px] flex-1 max-w-md">
    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
        @include('icons.search-01')
    </span>

    <x-admin.form.input
        type="text"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="pr-9"
    />
</div>
