{{--
    3D kit — $kit3d: ['texture' => ..., 'accent' => '#..', 'secondary' => '#..', 'caption' => [fa,en]?]
--}}
@php($locale = 'en')
<div class="reveal mx-auto w-full max-w-lg">
    <div data-kit3d
         data-texture="{{ asset($kit3d['texture']) }}"
         data-accent="{{ $kit3d['accent'] ?? '#00ff7a' }}"
         data-secondary="{{ $kit3d['secondary'] ?? '#1a1a1e' }}"
         class="kit3d group relative aspect-square cursor-grab select-none overflow-hidden rounded-3xl border border-edge/70 bg-surface/50 touch-none"
         aria-label="{{ $locale === 'en' ? '3D kit viewer' : '3D kit view' }}">
        <canvas class="block size-full"></canvas>
        <span class="font-num pointer-events-none absolute right-4 top-4 rounded-full bg-accent/90 px-3 py-1 text-xs font-black text-page shadow-[0_0_20px_-4px_var(--landing-accent)]">3D</span>
        <span class="pointer-events-none absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full border border-edge bg-page/70 px-4 py-1.5 text-[11px] font-bold text-muted backdrop-blur transition-colors group-hover:text-accent">
            {{ $locale === 'en' ? 'Drag to rotate the shirt' : 'Drag to rotate the kit' }} ✥
        </span>
    </div>
    @if(!empty($kit3d['caption']))
        <p class="mt-4 text-center text-sm text-muted">{{ $kit3d['caption'][$locale === 'en' ? 'en' : 'fa'] ?? $kit3d['caption'] }}</p>
    @endif
</div>
