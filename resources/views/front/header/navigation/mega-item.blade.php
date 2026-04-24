@php
    $columns = max(1, min((int) $item['mega_menu_columns'], 5));
@endphp

<div class="relative group flex items-center">
    <a href="{{ $item['url'] }}"
       target="{{ $item['target'] }}"
       @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:text-slate-900 hover:bg-slate-50 transition {{ $item['css_class'] }}">
        @if($item['icon'])
            <span class="text-base">{{ $item['icon'] }}</span>
        @endif

        <span>{{ $item['title'] }}</span>

        @if($item['badge'])
            <span
                class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-extrabold text-brand-700 ring-1 ring-brand-100">
                {{ $item['badge'] }}
            </span>
        @endif

        <svg class="h-4 w-4 text-slate-400 group-hover:text-slate-600 transition"
             viewBox="0 0 24 24" fill="none">
            <path d="M6 9l6 6 6-6"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
        </svg>
    </a>

    <div class="absolute right-0 top-full z-40 mt-3 w-[min(1080px,92vw)]
        opacity-0 invisible translate-y-2 pointer-events-none
        group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
        transition duration-200">
        <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-2xl">
            <div class="grid grid-cols-12">

                <div class="col-span-12 lg:col-span-9 p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">
                                {{ $item['mega_menu_title'] ?: $item['title'] }}
                            </div>
                            <div class="mt-1 text-xs font-medium text-slate-500">
                                {{ $item['mega_menu_description'] ?? '' }}
                            </div>
                        </div>

                        <a href="{{ $item['url'] }}"
                           class="shrink-0 rounded-2xl bg-slate-100 px-3 py-2 text-xs font-extrabold text-slate-700 hover:bg-slate-200 transition">
                            مشاهده همه
                        </a>
                    </div>

                    <div @class([
                        'grid gap-4',
                        'grid-cols-1' => $columns === 1,
                        'grid-cols-2' => $columns === 2,
                        'grid-cols-2 md:grid-cols-3' => $columns === 3,
                        'grid-cols-2 md:grid-cols-4' => $columns === 4,
                        'grid-cols-2 md:grid-cols-5' => $columns >= 5,
                    ])>
                        @foreach($item['children'] as $child)
                            @include('front.header.navigation.mega-card', [
                                'item' => $child,
                                'level' => 1,
                            ])
                        @endforeach
                    </div>
                </div>

                <div
                    class="col-span-12 lg:col-span-3 border-t border-slate-200 bg-slate-50/80 p-6 lg:border-r lg:border-t-0">
                    <div class="text-sm font-extrabold text-slate-900">
                        دسترسی سریع
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach($item['children']->take(4) as $quickItem)
                            <a href="{{ $quickItem['url'] }}"
                               target="{{ $quickItem['target'] }}"
                               @if($quickItem['target'] === '_blank') rel="noopener noreferrer" @endif
                               class="flex items-center gap-3 rounded-2xl bg-white p-3 text-sm font-bold text-slate-700 ring-1 ring-slate-200 hover:text-slate-900 hover:shadow-md transition">
                                @if($quickItem['icon'])
                                    <span
                                        class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-50 text-base">
                                        {{ $quickItem['icon'] }}
                                    </span>
                                @endif

                                <span class="line-clamp-1">{{ $quickItem['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
