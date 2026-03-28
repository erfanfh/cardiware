@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if($label)
        <label
            class="text-xs font-medium text-gray-500 dark:text-gray-400"
            @if($for) for="{{ $for }}" @endif
        >
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
