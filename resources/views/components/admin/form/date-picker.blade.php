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
            'class' => 'w-full h-10 rounded-2xl border border-slate-200 bg-white pr-10 pl-3 text-sm text-slate-800 placeholder:text-slate-400 outline-none transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300'
        ]) }}
    />

    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
        @include('icons.calendar-03')
    </div>
</div>
