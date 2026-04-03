@props([
    'title' => 'مدیریت و جستجو'
])

<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="min-w-0">
        <h2 class="text-sm font-bold text-slate-600">
            {{ $title }}
        </h2>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <x-admin.form.button
            type="button"
            variant="secondary"
            x-on:click="open = !open"
            x-bind:aria-expanded="open"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M4 6h16M7 12h10M10 18h4" stroke-width="1.8" stroke-linecap="round"/>
            </svg>

            <span x-text="open ? 'بستن فیلترها' : 'فیلترها'"></span>
        </x-admin.form.button>

        {{ $actions ?? '' }}
    </div>
</div>
