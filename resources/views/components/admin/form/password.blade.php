@props([
    'name' => null,
    'type' => 'text',
    'value' => '',
    'toggle' => 'password',
])

<div class="relative">
    <input
        type="{{ $type }}"
        value="{{ $value }}"
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full h-10 rounded-2xl border border-slate-200 bg-white pr-3 pl-10 text-sm text-slate-800 placeholder:text-slate-400 outline-none transition focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300'
        ]) }}
    />

    <span class="absolute left-3 top-2.5 cursor-pointer" onclick="togglePasswordVisibility('{{ $toggle }}', 'togglePassword')">
        @include('icons.view')
    </span>
</div>

