@php
    $level = $level ?? 1;
@endphp

<div class="relative group/sub">
    <a href="{{ $item['url'] }}"
       target="{{ $item['target'] }}"
       @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
       class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-brand-500 hover:text-white transition dark:text-slate-300 dark:hover:bg-brand-600 dark:hover:text-white hover:[&_svg]:text-white [&_svg]:transition {{ $item['css_class'] }}">
        <x-front.navigation-icon :item="$item" class="h-5 w-5 text-current [&_svg]:h-5 [&_svg]:w-5" />

        <span class="flex-1 line-clamp-1">{{ $item['title'] }}</span>

        @if($item['badge'])
            <span
                class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-extrabold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                {{ $item['badge'] }}
            </span>
        @endif

        @if($item['children']->isNotEmpty())
            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500"
                 viewBox="0 0 24 24" fill="none">
                <path d="M15 6l-6 6 6 6"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"/>
            </svg>
        @endif
    </a>

    @if($item['children']->isNotEmpty())
        <div class="absolute right-full -top-2 z-50 min-w-64 pr-2
            opacity-0 invisible translate-x-2 scale-[0.98] pointer-events-none
            group-hover/sub:opacity-100 group-hover/sub:visible group-hover/sub:translate-x-0 group-hover/sub:scale-100 group-hover/sub:pointer-events-auto
            transition-all duration-200 ease-out">
            <div
                class="rounded-3xl border border-slate-100 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30">
                @foreach($item['children'] as $child)
                    @include('front.header.navigation.dropdown-sub-item', [
                        'item' => $child,
                        'level' => $level + 1,
                    ])
                @endforeach
            </div>
        </div>
    @endif
</div>
