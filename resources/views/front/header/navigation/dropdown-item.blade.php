<div class="relative group flex items-center">
    <a href="{{ $item['url'] }}"
       target="{{ $item['target'] }}"
       @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:text-slate-900 hover:bg-slate-50 transition dark:hover:bg-slate-800 dark:hover:text-slate-100 {{ $item['css_class'] }}">
        <x-front.navigation-icon :item="$item" class="h-4 w-4 text-current [&_svg]:h-4 [&_svg]:w-4" />

        <span>{{ $item['title'] }}</span>

        @if($item['badge'])
            <span
                class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-extrabold text-brand-700 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                {{ $item['badge'] }}
            </span>
        @endif

        <svg
            class="h-4 w-4 text-slate-400 group-hover:text-slate-600 transition dark:text-slate-500 dark:group-hover:text-slate-300"
            viewBox="0 0 24 24" fill="none">
            <path d="M6 9l6 6 6-6"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
        </svg>
    </a>

    <div class="absolute left-0 top-full z-40 min-w-64 pt-3
        opacity-0 invisible translate-y-2 scale-[0.98] pointer-events-none
        group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:scale-100 group-hover:pointer-events-auto
        transition-all duration-200 ease-out">
        <div
            class="rounded-3xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30">
            @foreach($item['children'] as $child)
                @include('front.header.navigation.dropdown-sub-item', [
                    'item' => $child,
                    'level' => 1,
                ])
            @endforeach
        </div>
    </div>
</div>
