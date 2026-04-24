@php
    $columns = max(1, min((int) $item['mega_menu_columns'], 6));
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

    <div class="absolute right-0 top-full z-40 mt-3 w-[min(980px,92vw)]
        opacity-0 invisible translate-y-2 pointer-events-none
        group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
        transition duration-200">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="text-sm font-extrabold text-slate-900">
                        {{ $item['mega_menu_title'] ?: $item['title'] }}
                    </div>

                    <a href="{{ $item['url'] }}"
                       class="text-xs font-bold text-brand-600 hover:text-brand-700 transition">
                        مشاهده همه
                    </a>
                </div>

                <div @class([
                    'grid gap-3',
                    'grid-cols-1' => $columns === 1,
                    'grid-cols-2' => $columns === 2,
                    'grid-cols-2 md:grid-cols-3' => $columns === 3,
                    'grid-cols-2 md:grid-cols-4' => $columns === 4,
                    'grid-cols-2 md:grid-cols-5' => $columns === 5,
                    'grid-cols-2 md:grid-cols-6' => $columns >= 6,
                ])>
                    @foreach($item['children'] as $child)
                        <a href="{{ $child['url'] }}"
                           target="{{ $child['target'] }}"
                           @if($child['target'] === '_blank') rel="noopener noreferrer" @endif
                           class="group/item rounded-2xl bg-slate-50 p-3 hover:bg-white hover:shadow-md hover:ring-1 hover:ring-slate-200 transition {{ $child['css_class'] }}">
                            <div class="flex items-center gap-3">
                                @if($child['icon'])
                                    <span
                                        class="h-10 w-10 shrink-0 rounded-2xl bg-white ring-1 ring-slate-200 grid place-items-center text-base">
                                        {{ $child['icon'] }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 line-clamp-1">
                                        {{ $child['title'] }}
                                    </div>

                                    @if($child['badge'])
                                        <div class="mt-1">
                                            <span
                                                class="rounded-full bg-white px-2 py-0.5 text-[10px] font-extrabold text-slate-500 ring-1 ring-slate-200">
                                                {{ $child['badge'] }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
