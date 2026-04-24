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
</a>
