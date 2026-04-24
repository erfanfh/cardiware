<div
    class="group/card rounded-3xl border border-slate-100 bg-slate-50 p-4 hover:bg-white hover:shadow-md hover:border-slate-200 transition {{ $item['css_class'] }}">
    <div class="flex items-start gap-3">
        @if($item['icon'])
            <span
                class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-lg ring-1 ring-slate-200 group-hover/card:ring-brand-100 transition">
                {{ $item['icon'] }}
            </span>
        @endif

        <div class="min-w-0 flex-1">
            <a href="{{ $item['url'] }}"
               target="{{ $item['target'] }}"
               @if($item['target'] === '_blank') rel="noopener noreferrer" @endif
               class="flex items-center gap-2">
                <span
                    class="line-clamp-1 text-sm font-extrabold text-slate-900 hover:text-brand-700 transition inline-block w-full">
                    {{ $item['title'] }}
                </span>

                @if($item['badge'])
                    <span
                        class="shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-extrabold text-brand-700 ring-1 ring-brand-100">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>

            @if($item['children']->isNotEmpty())
                <div class="mt-3 space-y-1.5">
                    @foreach($item['children'] as $child)
                        @include('front.header.navigation.mega-sub-link', [
                            'item' => $child,
                            'level' => 2,
                        ])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
