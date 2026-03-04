@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <label class="block text-xs font-bold text-slate-700">
            {{ $label }}
            @if($required)
                <span class="text-rose-600">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($hint)
        <div class="text-[11px] text-slate-500 leading-5">{{ $hint }}</div>
    @endif

    @if($error)
        <div class="text-[11px] text-rose-600 leading-5 font-semibold">{{ $error }}</div>
    @endif
</div>
