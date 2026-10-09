{{--
    Controls for one watchlist on the launcher-devices page. Every other list's current
    settings are carried along as hidden inputs, so changing one list never resets another.
    Params: $prefix (r|w|c), $title, $minLabel, $count (rows shown).
--}}
@php
    $f = $listFilters[$prefix];
    $more = $listHasMore[$prefix];
    $nextLimit = $f['limit'] === 25 ? 100 : 500;
@endphp
<form method="GET" action="{{ route('admin.launcher-devices.index') }}"
      class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs shadow-sm">
    @foreach ($listFilters as $otherPrefix => $other)
        @continue($otherPrefix === $prefix)
        @foreach ($other as $key => $value)
            <input type="hidden" name="{{ $otherPrefix }}_{{ $key }}" value="{{ $value }}">
        @endforeach
    @endforeach

    <span class="ml-auto font-black text-slate-800">{{ $title }}
        <span class="font-bold text-slate-500">— {{ $count }} مورد{{ $more ? ' (موارد بیشتری هست)' : '' }}</span>
    </span>

    <label class="flex flex-col gap-1">
        <span class="font-bold text-slate-500">بازه</span>
        <select name="{{ $prefix }}_days" class="rounded-lg border border-slate-200 px-2 py-1">
            @foreach ([7, 30, 90, 180, 365] as $days)
                <option value="{{ $days }}" @selected($f['days'] === $days)>{{ $days }} روز</option>
            @endforeach
        </select>
    </label>
    <label class="flex flex-col gap-1">
        <span class="font-bold text-slate-500">{{ $minLabel }}</span>
        <input type="number" min="1" max="1000" name="{{ $prefix }}_min" value="{{ $f['min'] }}"
               class="w-20 rounded-lg border border-slate-200 px-2 py-1">
    </label>
    <label class="flex flex-col gap-1">
        <span class="font-bold text-slate-500">مرتب‌سازی</span>
        <select name="{{ $prefix }}_sort" class="rounded-lg border border-slate-200 px-2 py-1">
            <option value="count" @selected($f['sort'] === 'count')>بیشترین تعداد</option>
            <option value="latest" @selected($f['sort'] === 'latest')>جدیدترین</option>
        </select>
    </label>
    <label class="flex flex-col gap-1">
        <span class="font-bold text-slate-500">تعداد نمایش</span>
        <select name="{{ $prefix }}_limit" class="rounded-lg border border-slate-200 px-2 py-1">
            @foreach ([25, 100, 500] as $limit)
                <option value="{{ $limit }}" @selected($f['limit'] === $limit)>{{ $limit }}</option>
            @endforeach
        </select>
    </label>
    <button class="rounded-lg bg-slate-900 px-3 py-1.5 font-bold text-white">اعمال</button>
    @if ($more && $f['limit'] < 500)
        <button name="{{ $prefix }}_limit" value="{{ $nextLimit }}"
                class="rounded-lg border border-slate-300 px-3 py-1.5 font-bold text-slate-700">نمایش بیشتر ({{ $nextLimit }})</button>
    @endif
</form>
