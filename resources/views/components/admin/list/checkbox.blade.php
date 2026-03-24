@props([
    'name' => null,
    'value' => 1,
    'checked' => false,
])

<label class="inline-flex cursor-pointer items-center justify-center">
    <input
        type="checkbox"
        @if($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        @checked($checked)
        {{ $attributes->merge([
            'class' => 'h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0'
        ]) }}
    >
</label>
