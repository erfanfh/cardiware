@php($locale = $locale ?? 'fa')
<div x-data="{ active: null }">
    <h2 class="text-2xl font-black text-ink">{{ $locale === 'en' ? 'Stadiums' : 'استادیوم‌ها' }}</h2>

    <div class="mt-8 grid gap-5 sm:grid-cols-2">
        @foreach($pageData['catalog'] as $i => $stadium)
            <button @click="active = '{{ asset($stadium['image']) }}'"
                    class="group overflow-hidden rounded-2xl border border-edge/70 bg-surface/60 text-start transition-all duration-300 hover:-translate-y-0.5 hover:border-accent/40">
                <div class="relative aspect-video overflow-hidden">
                    <img src="{{ asset($stadium['image']) }}" alt="{{ $stadium['name'] }}" class="size-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                    <span class="font-num absolute bottom-3 right-3 rounded-lg bg-black/60 px-3 py-1 text-xs font-black text-white backdrop-blur">@php($faDigits = ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹', ','=>'٬']){{ $locale === 'en' ? number_format($stadium['capacity']) : strtr(number_format($stadium['capacity']), $faDigits) }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 p-5">
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-extrabold text-ink group-hover:text-accent">{{ $stadium['name'] }}</h3>
                        <p class="mt-1 text-xs text-muted">{{ $stadium['city'] }} — {{ $stadium['team'] }}</p>
                    </div>
                    @include('front.landings.partials.icon', ['name' => 'stadium', 'class' => 'size-6 shrink-0 text-muted group-hover:text-accent'])
                </div>
            </button>
        @endforeach
    </div>

    {{-- لایت‌باکس --}}
    <div x-show="active" x-cloak x-transition.opacity class="fixed inset-0 z-[60] grid place-items-center bg-black/90 p-4 backdrop-blur" @click.self="active = null" @keydown.escape.window="active = null">
        <div class="relative max-w-4xl">
            <img :src="active" alt="" class="w-full rounded-2xl border border-edge">
            <button @click="active = null" class="absolute -top-12 left-0 grid size-9 place-items-center rounded-full border border-edge bg-surface text-ink" aria-label="close">
                @include('front.landings.partials.icon', ['name' => 'x', 'class' => 'size-4'])
            </button>
        </div>
    </div>
</div>
