<div {{ $attributes->merge([
    'class' => 'mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5 justify-end'
]) }}>
    {{ $slot }}
</div>
