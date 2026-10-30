<section id="pricing" class="section-pad relative scroll-mt-24 overflow-hidden">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute left-1/2 top-0 h-[28rem] w-[52rem] -translate-x-1/2 rounded-full blur-3xl"
             style="background: radial-gradient(closest-side, var(--landing-glow), transparent)"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @include('front.landings.partials.section-head', [
            'eyebrow' => $section['eyebrow'] ?? null,
            'title' => $section['title'],
            'subtitle' => $section['subtitle'] ?? null,
        ])

        <div class="mt-14 grid gap-6 md:grid-cols-2 lg:mx-auto lg:max-w-4xl {{ count($section['plans']) > 1 ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }}">
            @foreach($section['plans'] as $plan)
                <article class="reveal relative overflow-hidden rounded-3xl border border-accent/30 bg-surface/80 p-8 shadow-[0_0_80px_-30px_var(--landing-accent)] backdrop-blur">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-accent via-accent-2 to-accent-3"></div>

                    @if(!empty($plan['badge']))
                        <span class="absolute left-6 top-6 rounded-full bg-accent px-3 py-1 text-xs font-black text-page">{{ $plan['badge'] }}</span>
                    @endif

                    <h3 class="text-xl font-extrabold text-ink">{{ $plan['name'] }}</h3>

                    <div class="mt-5 flex items-end gap-2">
                        @if(!empty($plan['old_price']))
                            <span class="font-num mb-2 text-sm text-muted line-through">{{ $plan['old_price'] }}</span>
                        @endif
                        <span class="font-num text-5xl font-black leading-none text-ink">{{ $plan['price'] }}</span>
                        <span class="mb-1 text-sm font-medium text-muted">{{ $plan['period'] }}</span>
                    </div>

                    <ul class="mt-8 space-y-3.5 border-t border-edge/60 pt-8">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-start gap-3 text-sm leading-6 text-muted">
                                <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-accent/15 text-accent">
                                    @include('front.landings.partials.icon', ['name' => 'check', 'class' => 'size-3'])
                                </span>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ $data['buy_url'] === '#pricing' ? $data['product_url'] : $data['buy_url'] }}" class="btn-primary mt-8 w-full justify-center">
                        {{ $plan['cta'] }}
                    </a>
                </article>
            @endforeach
        </div>

        @if(!empty($section['note']))
            <p class="reveal mt-8 text-center text-xs text-muted">{{ $section['note'] }}</p>
        @endif
    </div>
</section>
