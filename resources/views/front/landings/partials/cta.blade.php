<section class="section-pad relative overflow-hidden">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal relative overflow-hidden rounded-3xl border border-accent/25 bg-surface/70 px-6 py-16 text-center backdrop-blur sm:px-16">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute inset-0 bg-grid opacity-30"></div>
                <div class="absolute -top-32 left-1/2 h-72 w-[40rem] -translate-x-1/2 rounded-full blur-3xl"
                     style="background: radial-gradient(closest-side, var(--landing-glow), transparent)"></div>
            </div>

            <div class="relative">
                <h2 class="text-3xl font-black leading-snug text-ink sm:text-4xl">{{ $section['title'] }}</h2>
                <p class="mx-auto mt-4 max-w-xl text-base leading-8 text-muted">{{ $section['subtitle'] }}</p>

                <div class="mt-9 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ $section['primary']['href'] }}" class="btn-primary">
                        {{ $section['primary']['label'] }}
                    </a>
                    <a href="{{ $section['secondary']['href'] }}" class="btn-ghost">
                        {{ $section['secondary']['label'] }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
