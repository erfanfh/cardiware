<div {{ $attributes->merge([
    'class' => 'flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft sm:flex-row sm:items-center sm:justify-between'
]) }}>
    {{ $slot }}
</div>
