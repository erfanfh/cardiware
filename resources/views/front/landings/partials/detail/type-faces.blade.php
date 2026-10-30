@php($locale = $locale ?? 'fa')
<div class="space-y-14">
    <div>
        <h2 class="text-2xl font-black text-ink">{{ $locale === 'en' ? 'Before / After' : 'قبل و بعد' }}</h2>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-muted">
            {{ $locale === 'en'
                ? 'Every face is rebuilt from reference photography — hair, beard, skin tone and accessories. Swipe the gallery for real examples.'
                : 'هر چهره از روی عکس‌های مرجع بازسازی شده — مو، ریش، رنگ پوست و اکسسوری‌ها. گالری را برای نمونه‌های واقعی ببینید.' }}
        </p>
    </div>

    {{-- کارت‌های بازیکن با چرخش سه‌بعدی (tilt) — با حرکت موس بچرخند --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        @foreach([
            ['num' => '۱۰', 'pos' => ['fa' => 'مهاجم', 'en' => 'Forward'], 'rating' => '۸۶'],
            ['num' => '۸', 'pos' => ['fa' => 'هافبک', 'en' => 'Midfielder'], 'rating' => '۸۲'],
            ['num' => '۱', 'pos' => ['fa' => 'دروازه‌بان', 'en' => 'Goalkeeper'], 'rating' => '۷۹'],
        ] as $p)
            <div data-tilt="14" class="tilt-card group relative select-none rounded-3xl border border-edge/70 bg-gradient-to-b from-surface-2 to-surface/80 p-6 text-center">
                <div data-tilt-inner>
                    <div class="flex items-start justify-between">
                        <span class="font-num text-3xl font-black text-ink">{{ $p['num'] }}</span>
                        <span class="font-num grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-accent to-accent-2 text-lg font-black text-page shadow-[0_0_24px_-6px_var(--landing-accent)]">{{ $p['rating'] }}</span>
                    </div>
                    <span class="mx-auto mt-4 grid size-24 place-items-center rounded-full border border-edge/60 bg-page/60">
                        @include('front.landings.partials.icon', ['name' => 'face', 'class' => 'size-12 text-muted transition-colors group-hover:text-accent'])
                    </span>
                    <p class="mt-5 text-sm font-extrabold text-ink">{{ $p['pos'][$locale === 'en' ? 'en' : 'fa'] }}</p>
                    <p class="mt-1 text-[11px] text-muted">{{ $locale === 'en' ? 'Scanned 3D head + miniface' : 'سر سه‌بعدی اسکن‌شده + مینی‌فیس' }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        <div class="swiper" data-gallery>
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="{{ asset('images/landings/shared/g4-faces.svg') }}" alt="faces" class="aspect-video w-full rounded-2xl border border-edge/70 object-cover" loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="{{ asset('images/landings/shared/g2-gameplay.svg') }}" alt="gameplay" class="aspect-video w-full rounded-2xl border border-edge/70 object-cover" loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="{{ asset('images/landings/shared/g1-menu.svg') }}" alt="menu" class="aspect-video w-full rounded-2xl border border-edge/70 object-cover" loading="lazy">
                </div>
            </div>
            <div class="swiper-pagination !relative !bottom-auto mt-6"></div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach($locale === 'en'
            ? ['Scan-based 3D heads', 'Matching menu minifaces', 'Updated after every transfer window']
            : ['سرهای سه‌بعدی اسکن‌شده', 'مینی‌فیس هماهنگ منوها', 'به‌روزرسانی بعد از هر پنجره ترنسفر'] as $point)
            <div class="flex items-center gap-3 rounded-2xl border border-edge/70 bg-surface/60 p-4 text-sm font-bold text-ink">
                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-accent/15 text-accent">
                    @include('front.landings.partials.icon', ['name' => 'check', 'class' => 'size-4'])
                </span>
                {{ $point }}
            </div>
        @endforeach
    </div>
</div>
