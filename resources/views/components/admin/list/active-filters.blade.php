@props([
    'items' => [],
    'clearUrl' => null,
])

@if(!empty($items))
    <div {{ $attributes->merge([
        'class' => 'flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft'
    ]) }}>
        <span class="text-xs font-bold text-slate-500">
            فیلترهای فعال:
        </span>

        @foreach($items as $item)
            @if(!empty($item['label']) && !empty($item['remove_url']))
                <a
                    href="{{ $item['remove_url'] }}"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-200"
                >
                    <span>{{ $item['label'] }}</span>

                    <span class="text-slate-400">×</span>
                </a>
            @endif
        @endforeach

        @if($clearUrl)
            <a
                href="{{ $clearUrl }}"
                class="mr-1 inline-flex items-center gap-2 text-xs font-medium text-rose-600 transition hover:text-rose-700"
            >
                حذف همه فیلترها
            </a>
        @endif
    </div>
@endif
