@props([
    'name' => null,
    'value' => '',
    'placeholder' => 'انتخاب تاریخ',
])

<div class="relative">
    <input
        type="text"
        value="{{ $value }}"
        @if($name) name="{{ $name }}" @endif
        placeholder="{{ $placeholder }}"
        data-jdp
        autocomplete="off"
        {{ $attributes->merge([
            'class' => 'w-full pr-10 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:ring-2 focus:ring-orange-200 focus:border-orange-300 placeholder:text-slate-400 cursor-pointer'
        ]) }}
    />

    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
        @include('icons.calendar-03')
    </div>
</div>
