<div {{ $attributes->merge(['class' => 'mt-2 flex flex-wrap items-center gap-3 text-xs font-bold transition lg:translate-y-1 lg:pointer-events-none lg:opacity-0 group-hover:lg:translate-y-0 group-hover:lg:pointer-events-auto group-hover:lg:opacity-100']) }}>
    {{ $slot }}
</div>
