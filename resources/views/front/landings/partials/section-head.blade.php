{{-- Shared section heading — $eyebrow / $title / $subtitle --}}
<div class="reveal mx-auto max-w-2xl text-center">
    @if(!empty($eyebrow))
        <span class="eyebrow">{{ $eyebrow }}</span>
    @endif
    <h2 class="mt-4 text-3xl font-black leading-snug text-ink sm:text-4xl">{{ $title }}</h2>
    @if(!empty($subtitle))
        <p class="mt-4 text-base leading-8 text-muted">{{ $subtitle }}</p>
    @endif
</div>
