{{--
    اسپینر ۳۶۰° — $spin: ['dir' => 'spin/trophy', 'frames' => 36, 'caption' => [fa,en]?]
    فریم‌ها: public/images/landings/{dir}/f00..f35.png
--}}
@php($locale = $locale ?? 'fa')
@php($frames = $spin['frames'] ?? 36)
<div class="reveal mx-auto w-full max-w-md" @php($capt = $spin['caption'][ $locale === 'en' ? 'en' : 'fa' ] ?? ($locale === 'en' ? '360° view' : 'نمای ۳۶۰°'))>
    <div data-spinner
         data-base="{{ asset('images/landings/'.($spin['dir'] ?? 'spin/trophy')) }}"
         data-frames="{{ $frames }}"
         class="spinner group relative aspect-square select-none overflow-hidden rounded-3xl border border-edge/70 bg-surface/50"
         tabindex="0" role="slider" aria-label="{{ $capt }}">
        <img src="{{ asset('images/landings/'.($spin['dir'] ?? 'spin/trophy').'/f00.png') }}" alt="{{ $capt }}" draggable="false" class="pointer-events-none size-full object-contain p-6">
        <span class="font-num pointer-events-none absolute right-4 top-4 rounded-full bg-accent/90 px-3 py-1 text-xs font-black text-page shadow-[0_0_20px_-4px_var(--landing-accent)]">360°</span>
        <span class="pointer-events-none absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full border border-edge bg-page/70 px-4 py-1.5 text-[11px] font-bold text-muted backdrop-blur transition-colors group-hover:text-accent">
            {{ $locale === 'en' ? 'Drag to rotate' : 'بکش و بچرخون' }} ✥
        </span>
    </div>
    @if(!empty($spin['caption']))
        <p class="mt-4 text-center text-sm text-muted">{{ $capt }}</p>
    @endif
</div>
