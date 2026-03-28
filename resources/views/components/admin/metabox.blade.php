@props([
    'title' => '',
    'icon' => null,
    'actions' => null,
    'collapsible' => true,
    'defaultOpen' => true,
    'storageKey' => null,
])

@php
    $key = $storageKey ?: ('metabox:' . md5($title));
    $coll = (bool) $collapsible;
    $def  = (bool) $defaultOpen;
@endphp
<section
    {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 shadow-sm overflow-hidden ']) }}
    x-data="{
        open: {{ $def ? 'true' : 'false' }},
        collapsible: {{ $coll ? 'true' : 'false' }},
        key: @js($key),

        init() {
            if (!this.collapsible) return;

            try {
                const v = localStorage.getItem(this.key);
                if (v !== null) this.open = (v === '1');
            } catch (e) {}
        },

        toggle() {
            if (!this.collapsible) return;

            this.open = !this.open;
            try {
                localStorage.setItem(this.key, this.open ? '1' : '0');
            } catch (e) {}
        }
    }"
>
    <header class="px-4 py-3 border-b border-slate-200 dark:border-gray-700 flex items-center justify-between gap-3">
        <button
            type="button"
            class="flex items-center gap-2 min-w-0 text-right hover:opacity-80 transition"
            @click="toggle()"
            :aria-expanded="open.toString()"
        >
            @if($icon)
                <span class="shrink-0 text-slate-500">{!! $icon !!}</span>
            @endif

            <h3 class="font-extrabold text-sm text-slate-800 truncate dark:text-gray-200 leading-7">{{ $title }}</h3>
        </button>

        <div class="shrink-0 flex items-center gap-2">
            @if($actions)
                {{ $actions }}
            @endif

            <template x-if="collapsible">
                <button
                    type="button"
                    class="h-9 w-9 cursor-pointer rounded-2xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-slate-100 transition grid place-items-center text-slate-600 dark:text-white dark:hover:bg-gray-700"
                    @click="toggle()"
                    title="باز/بسته"
                >
                    <span x-show="open" x-cloak>−</span>
                    <span x-show="!open" x-cloak>+</span>
                </button>
            </template>
        </div>
    </header>

    <div class="p-4" x-show="!collapsible || open" x-cloak>
        {{ $slot }}
    </div>
</section>
