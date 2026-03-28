<div {{ $attributes->merge([
    'class' => 'mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft'
]) }}>
    {{ $slot }}
</div>
