<section id="hero" class="relative overflow-hidden" x-data="{ video: false }">

    {{-- Background layers --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute inset-0 bg-grid opacity-40"></div>
        <div class="absolute -top-48 left-1/2 h-[38rem] w-[76rem] -translate-x-1/2 rounded-full blur-3xl"
             style="background: radial-gradient(closest-side, var(--landing-glow), transparent)"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-b from-transparent to-page"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-32 text-center sm:px-6 lg:px-8 lg:pt-40">

        {{-- Published badge --}}
        <div class="reveal mx-auto inline-flex items-center gap-2.5 rounded-full border border-accent/30 bg-accent/10 px-4 py-1.5 text-sm font-semibold text-accent backdrop-blur">
            <span class="relative flex size-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent opacity-60"></span>
                <span class="relative inline-flex size-2 rounded-full bg-accent"></span>
            </span>
            {{ $section['badge'] }}
        </div>

        {{-- Title --}}
        <h1 class="reveal mt-7 text-4xl font-black leading-[1.15] text-ink sm:text-6xl lg:text-7xl">
            {{ $section['title'] }}
            <span class="text-gradient block sm:inline">{{ $section['highlight'] }}</span>
        </h1>

        <p class="reveal mx-auto mt-6 max-w-2xl text-base leading-8 text-muted sm:text-lg sm:leading-9">
            {{ $section['subtitle'] }}
        </p>

        {{-- Buttons CTA --}}
        <div class="reveal mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ $data['buy_url'] }}" class="btn-primary">
                @include('front.landings.partials.icon', ['name' => 'bolt', 'class' => 'size-5'])
                Buy and download patch
            </a>
            <a href="{{ $data['launcher_url'] }}" class="btn-ghost">
                @include('front.landings.partials.icon', ['name' => 'download', 'class' => 'size-5'])
                Download MW Launcher
            </a>
        </div>

        {{-- Trust bullets --}}
        <div class="reveal mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-muted">
            @foreach($section['bullets'] as $bullet)
                <span class="inline-flex items-center gap-2">
                    @include('front.landings.partials.icon', ['name' => 'check', 'class' => 'size-4 text-accent'])
                    {{ $bullet }}
                </span>
            @endforeach
        </div>

        {{-- Hero artwork + Trailer --}}
        <div class="reveal relative mx-auto mt-16 max-w-5xl">
            <div class="glow-frame relative overflow-hidden rounded-2xl border border-edge/80">
                <img src="{{ asset($section['image']) }}" alt="{{ $data['product_name'] }}" class="w-full" loading="eager">

                {{-- Play trailer --}}
                <button @click="video = true" class="group absolute inset-0 grid place-items-center bg-black/25 transition-colors hover:bg-black/40" aria-label="{{ $section['video_title'] }}">
                    <span class="grid size-20 place-items-center rounded-full bg-accent/90 text-base shadow-[0_0_60px_-10px_var(--landing-accent)] transition-transform duration-300 group-hover:scale-110">
                        @include('front.landings.partials.icon', ['name' => 'play', 'class' => 'size-8 ltr:ml-1 rtl:mr-1'])
                    </span>
                </button>
            </div>

            {{-- Floating chips --}}
            @foreach($section['chips'] as $i => $chip)
                <div class="float-chip absolute hidden items-center gap-2.5 rounded-xl border border-edge bg-surface/90 px-4 py-2.5 text-sm font-bold text-ink shadow-xl backdrop-blur xl:flex"
                     style="{{ $i === 0 ? 'top:-8%; right:-6%;' : ($i === 1 ? 'top:46%; left:-9%;' : 'bottom:-7%; right:8%;') }} animation-delay: {{ $i * 0.8 }}s">
                    <span class="grid size-8 place-items-center rounded-lg bg-accent/15 text-accent">
                        @include('front.landings.partials.icon', ['name' => $chip['icon'], 'class' => 'size-4'])
                    </span>
                    {{ $chip['text'] }}
                </div>
            @endforeach
        </div>

        {{-- Trailer modal (placeholder; real video connected later) --}}
        <div x-show="video" x-cloak class="fixed inset-0 z-[60] grid place-items-center bg-black/85 p-4 backdrop-blur" @click.self="video = false" @keydown.escape.window="video = false">
            <div class="relative w-full max-w-4xl">
                <div class="grid aspect-video place-items-center rounded-2xl border border-edge bg-surface-2">
                    <div class="text-center">
                        <span class="mx-auto grid size-16 place-items-center rounded-full bg-accent/15 text-accent">
                            @include('front.landings.partials.icon', ['name' => 'play', 'class' => 'size-7'])
                        </span>
                        <p class="mt-4 font-bold text-ink">{{ $section['video_title'] }}</p>
                        <p class="mt-1 text-sm text-muted">Video placeholder: trailer coming soon</p>
                    </div>
                </div>
                <button @click="video = false" class="absolute -top-12 left-0 grid size-9 place-items-center rounded-full border border-edge bg-surface text-ink" aria-label="Close">
                    @include('front.landings.partials.icon', ['name' => 'x', 'class' => 'size-4'])
                </button>
            </div>
        </div>
    </div>
</section>
