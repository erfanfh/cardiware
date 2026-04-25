@php
    $level = $level ?? 2;
@endphp

<div>
    <a href="{{ $item['url'] }}"
       target="{{ $item['target'] }}"
       @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
       class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-xs font-bold text-slate-600 hover:bg-white hover:text-brand-700 transition dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-brand-300 {{ $item['css_class'] }}">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300 dark:bg-slate-600"></span>

        @if($item['icon'])
            <span class="text-sm">{{ $item['icon'] }}</span>
        @endif

        <span class="line-clamp-1">{{ $item['title'] }}</span>

        @if($item['badge'])
            <span
                class="mr-auto rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-extrabold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                {{ $item['badge'] }}
            </span>
        @endif
    </a>

    @if($item['children']->isNotEmpty())
        <div class="mr-4 mt-1 border-r border-slate-200 pr-2 dark:border-slate-700">
            @foreach($item['children'] as $child)
                @include('front.header.navigation.mega-sub-link', [
                    'item' => $child,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
