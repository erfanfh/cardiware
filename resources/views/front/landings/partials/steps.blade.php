<section id="install" class="section-pad scroll-mt-24 bg-surface/30">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
            'subtitle' => $section['subtitle'] ?? null,
        ])

        <ol class="relative mt-16 grid gap-10 lg:grid-cols-3 lg:gap-6">
            {{-- Desktop connecting line --}}
            <div class="pointer-events-none absolute inset-x-16 top-8 hidden h-px bg-gradient-to-l from-accent/10 via-accent/40 to-accent/10 lg:block" aria-hidden="true"></div>

            @foreach($section['items'] as $i => $item)
                <li class="reveal relative text-center">
                    <div class="relative z-10 mx-auto grid size-16 place-items-center rounded-2xl border border-accent/30 bg-surface shadow-[0_0_36px_-12px_var(--landing-accent)]">
                        @include('front.landings.partials.icon', ['name' => $item['icon'], 'class' => 'size-7 text-accent'])
                        <span class="font-num absolute -top-2.5 -right-2.5 grid size-6 place-items-center rounded-full bg-accent text-xs font-black text-page"> {{ $i + 1 }} </span>
                    </div>
                    <h3 class="mt-5 text-lg font-extrabold text-ink">{{ $item['title'] }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-sm leading-7 text-muted">{{ $item['desc'] }}</p>
                </li>
            @endforeach
        </ol>

        <div class="reveal mt-12 text-center">
            <a href="{{ $data['launcher_url'] }}" class="btn-primary">
                @include('front.landings.partials.icon', ['name' => 'download', 'class' => 'size-5'])
                Download MW Launcher
            </a>
        </div>
    </div>
</section>
