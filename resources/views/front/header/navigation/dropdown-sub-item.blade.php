@php
    $level = $level ?? 1;
@endphp

<div class="relative group/sub">
    <a href="{{ $item['url'] }}"
       target="{{ $item['target'] }}"
       @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
       class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition {{ $item['css_class'] }}">
        @if($item['icon'])
            <span class="text-base">{{ $item['icon'] }}</span>
        @endif

        <span class="flex-1 line-clamp-1">{{ $item['title'] }}</span>

        @if($item['badge'])
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-extrabold text-slate-600">
                {{ $item['badge'] }}
            </span>
        @endif

        @if($item['children']->isNotEmpty())
            <svg class="h-4 w-4 text-slate-400"
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
            <div class="rounded-3xl border border-slate-100 bg-white p-2 shadow-2xl">
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
