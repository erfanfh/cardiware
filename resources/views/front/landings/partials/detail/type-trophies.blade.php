@php($locale = $locale ?? 'fa')
<div>
    <h2 class="text-2xl font-black text-ink">{{ $locale === 'en' ? 'Trophy Room' : 'گالری جام‌ها' }}</h2>

    <div class="mt-8 grid gap-5 sm:grid-cols-2">
        @foreach($pageData['catalog'] as $trophy)
            <article class="group relative overflow-hidden rounded-2xl border border-edge/70 bg-surface/60 p-6 transition-all duration-300 hover:-translate-y-0.5 hover:border-accent/40">
                <div class="pointer-events-none absolute -left-12 -top-12 size-36 rounded-full opacity-0 blur-3xl transition-opacity duration-500 group-hover:opacity-100"
                     style="background: var(--landing-glow)"></div>

                <div class="relative flex items-start gap-4">
                    <span class="grid size-14 shrink-0 place-items-center rounded-2xl border border-accent/25 bg-accent/10 text-accent">
                        @include('front.landings.partials.icon', ['name' => 'trophy', 'class' => 'size-7'])
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-base font-extrabold text-ink">{{ $trophy['name'] }}</h3>
                        <span class="mt-1 inline-block rounded-lg bg-surface-2 px-2.5 py-0.5 text-[11px] font-bold text-muted">{{ $trophy['competition'] }}</span>
                        <p class="mt-2.5 text-sm leading-7 text-muted">{{ $trophy['detail'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    {{-- نمای ۳۶۰° جام قهرمانان --}}
    <div class="mt-20">
        <div class="mx-auto max-w-2xl text-center">
            <h3 class="text-2xl font-black text-ink">{{ $locale === 'en' ? 'The trophy in 360°' : 'جام قهرمانان در ۳۶۰ درجه' }}</h3>
            <p class="mt-3 text-sm leading-7 text-muted">
                {{ $locale === 'en'
                    ? 'The real in-game 3D model — drag to inspect it from every angle.'
                    : 'مدل سه‌بعدی واقعی داخل بازی — بکش تا از هر زاویه ببینیش.' }}
            </p>
        </div>
        <div class="mt-8">
            @include('front.landings.partials.spinner', ['spin' => [
                'dir' => 'spin/trophy',
                'frames' => 36,
                'caption' => ['fa' => 'جام قهرمانان اروپا — مدل داخل بازی', 'en' => 'UEFA Champions League trophy — in-game model'],
            ]])
        </div>
    </div>
</div>
