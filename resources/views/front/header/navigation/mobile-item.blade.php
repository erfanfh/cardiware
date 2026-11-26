@php
    $level = $level ?? 0;
    $hasChildren = $item['children']->isNotEmpty();
@endphp

<div
    x-data="{ expanded: false }"
    class="{{ $level > 0 ? 'mr-3 border-r border-slate-100 pr-3 dark:border-slate-800' : '' }}"
>
    <div class="flex items-center gap-2">
        <a href="{{ $item['url'] }}"
           target="{{ $item['target'] }}"
           @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
           class="flex min-w-0 flex-1 items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-100 {{ $item['css_class'] }}">
            @if(filled($item['icon']))
                <x-front.navigation-icon
                    :item="$item"
                    class="h-9 w-9 rounded-xl bg-slate-50 text-slate-600 ring-1 ring-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700 [&_svg]:h-5 [&_svg]:w-5"
                />
            @endif

            <span class="min-w-0 flex-1 line-clamp-1">
                {{ $item['title'] }}
            </span>

            @if($item['badge'])
                <span
                    class="shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-extrabold text-brand-700 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                    {{ $item['badge'] }}
                </span>
            @endif
        </a>

        @if($hasChildren)
            <button
                type="button"
                x-on:click="expanded = !expanded"
                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-slate-50 text-slate-500 ring-1 ring-slate-100 hover:bg-slate-100 transition dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700 dark:hover:bg-slate-700"
                :aria-expanded="expanded.toString()"
                aria-label="Toggle submenu"
            >
                <svg
                    class="h-4 w-4 transition-transform duration-200"
                    :class="{ 'rotate-180': expanded }"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <path d="M6 9l6 6 6-6"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </button>
        @endif
    </div>

    @if($hasChildren)
        <div
            x-show="expanded"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="mt-1 space-y-1"
        >
            @foreach($item['children'] as $child)
                @include('front.header.navigation.mobile-item', [
                    'item' => $child,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
