<div
    x-data="{ open: false }"
    class="relative inline-block text-right"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
>
    {{ $trigger }}

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute left-0 z-30 mt-2 min-w-[180px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft2 ring-1 ring-slate-100"
    >
        <div class="py-2">
            {{ $slot }}
        </div>
    </div>
</div>
