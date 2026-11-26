@extends('front.master')

@php
    use App\Domain\Content\Models\LandingPage;
    $seo = (array) ($page->seo ?? []);
    $dark = $theme['mode'] === 'dark';
    $metaTitle = trim((string) ($seo['meta_title'] ?? '')) ?: $page->title;
    $ogImage = LandingPage::safeUrl(data_get($seo, 'og_image.path'));
    $text = $dark ? 'text-white' : 'text-slate-900';
    $muted = $dark ? 'text-slate-300' : 'text-slate-600';
    $card = $dark ? 'border-white/10 bg-white/5' : 'border-slate-200 bg-white';
@endphp

@section('head')
    <title>{{ $metaTitle }} | {{ get_setting('general', 'title') }}</title>
    @if(filled($seo['meta_description'] ?? null))
        <meta name="description" content="{{ $seo['meta_description'] }}">
    @endif
    @if(!empty($seo['noindex']) || $isPreview)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:title" content="{{ $metaTitle }}">
    @if($ogImage)
        <meta property="og:image" content="{{ str_starts_with($ogImage, '/') ? url($ogImage) : $ogImage }}">
    @endif
    <style>
        .lp-root { --lp-primary: {{ $theme['primary'] }}; --lp-accent: {{ $theme['accent'] }}; }
        .lp-btn { background: var(--lp-primary); color: #fff; box-shadow: 0 14px 30px -12px var(--lp-primary); }
        .lp-btn:hover { filter: brightness(1.1); transform: translateY(-2px); }
        .lp-btn-ghost { border: 1px solid var(--lp-primary); color: var(--lp-primary); }
        .lp-btn-ghost:hover { background: color-mix(in srgb, var(--lp-primary) 12%, transparent); }
        .lp-accent { color: var(--lp-accent); }
        .lp-primary { color: var(--lp-primary); }
        .lp-band-accent { background: linear-gradient(135deg, color-mix(in srgb, var(--lp-primary) 22%, transparent), color-mix(in srgb, var(--lp-accent) 12%, transparent)); }
        .lp-band-contrast { background: var(--lp-primary); color: #fff; }
        .lp-glow { background: radial-gradient(circle at 30% 20%, color-mix(in srgb, var(--lp-primary) 35%, transparent), transparent 60%); }
    </style>
@endsection

@section('content')
    <main class="lp-root relative isolate overflow-hidden {{ $dark ? 'bg-slate-950' : 'bg-slate-50' }} {{ $text }}">
        @if($theme['bg_image'])
            <img src="{{ $theme['bg_image'] }}" alt="" class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover opacity-20" loading="lazy">
        @endif
        @if($theme['pattern'] !== 'none')
            <span class="mw-footer-pattern is-{{ $theme['pattern'] }} -z-10" aria-hidden="true"></span>
        @endif
        <div class="lp-glow pointer-events-none absolute inset-x-0 top-0 -z-10 h-[40rem]"></div>

        @if($isPreview)
            <div class="sticky top-0 z-30 bg-amber-400 px-4 py-2 text-center text-xs font-black text-slate-950">
                Admin preview — {{ $page->isPubliclyVisible() ? 'This page is published' : 'This page is not yet visible to users' }}
            </div>
        @endif

        @foreach($page->blocks ?? [] as $index => $block)
            @php
                $type = $block['type'] ?? 'text';
                $variant = $block['variant'] ?? 'default';
                $band = match ($variant) { 'accent' => 'lp-band-accent', 'contrast' => 'lp-band-contrast', default => '' };
                $bTitle = trim((string) ($block['title'] ?? ''));
                $bSubtitle = trim((string) ($block['subtitle'] ?? ''));
                $bBody = trim((string) ($block['body'] ?? ''));
                $bImage = LandingPage::safeUrl(data_get($block, 'image.path'));
                $buttons = array_values(array_filter([
                    ['text' => $block['button_text'] ?? null, 'link' => LandingPage::safeUrl($block['button_link'] ?? null), 'class' => 'lp-btn'],
                    ['text' => $block['button2_text'] ?? null, 'link' => LandingPage::safeUrl($block['button2_link'] ?? null), 'class' => 'lp-btn-ghost'],
                ], fn ($button) => filled($button['text']) && $button['link']));
                $sectionMuted = $variant === 'contrast' ? 'text-white/80' : $muted;
            @endphp

            <section class="{{ $band }} {{ $type === 'hero' ? 'py-16 sm:py-24' : 'py-12 sm:py-16' }}" id="block-{{ $index + 1 }}">
                <div class="mx-auto max-w-6xl px-4">
                    @if($type === 'hero')
                        <div class="grid items-center gap-10 {{ $bImage ? 'lg:grid-cols-2' : '' }}">
                            <div class="{{ $bImage ? 'text-center lg:text-start' : 'mx-auto max-w-3xl text-center' }}">
                                @if($bSubtitle !== '')
                                    <span class="inline-flex rounded-full border px-4 py-1.5 text-xs font-black lp-accent" style="border-color: var(--lp-accent);">{{ $bSubtitle }}</span>
                                @endif
                                <h1 class="mt-5 text-3xl font-black leading-tight sm:text-5xl">{{ $bTitle }}</h1>
                                @if($bBody !== '')
                                    <p class="mt-5 whitespace-pre-line text-sm leading-8 sm:text-base {{ $sectionMuted }}">{{ $bBody }}</p>
                                @endif
                                @if(!empty($block['countdown_at']) && strtotime($block['countdown_at']) > time())
                                    <div class="mt-6" data-lp-countdown="{{ \Illuminate\Support\Carbon::parse($block['countdown_at'])->toIso8601String() }}"></div>
                                @endif
                                @if($buttons)
                                    <div class="mt-8 flex flex-wrap gap-3 {{ $bImage ? 'justify-center lg:justify-start' : 'justify-center' }}">
                                        @foreach($buttons as $button)
                                            <a href="{{ $button['link'] }}" class="{{ $button['class'] }} inline-flex h-12 items-center rounded-2xl px-6 text-sm font-black transition">{{ $button['text'] }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if($bImage)
                                <img src="{{ $bImage }}" alt="{{ $bTitle }}" class="w-full rounded-3xl object-cover shadow-2xl" loading="eager">
                            @endif
                        </div>

                    @elseif($type === 'features')
                        @include('front.landing-pages.partials.heading')
                        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach(LandingPage::lines($block['items_text'] ?? '') as $item)
                                <div class="rounded-3xl border p-6 {{ $card }}">
                                    @if(count($item) >= 3)
                                        <div class="text-3xl">{{ $item[0] }}</div>
                                        <div class="mt-3 text-base font-black">{{ $item[1] }}</div>
                                        <p class="mt-2 text-sm leading-7 {{ $muted }}">{{ $item[2] }}</p>
                                    @else
                                        <div class="text-base font-black">{{ $item[0] }}</div>
                                        @if(isset($item[1]))<p class="mt-2 text-sm leading-7 {{ $muted }}">{{ $item[1] }}</p>@endif
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    @elseif($type === 'products')
                        @include('front.landing-pages.partials.heading')
                        @php
                            $blockProducts = collect(preg_split('/[\s,،]+/u', (string) ($block['product_ids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
                                ->map(fn ($id) => $products->get((int) $id))->filter();
                        @endphp
                        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($blockProducts as $product)
                                <x-front.product.card :product="$product"/>
                            @endforeach
                        </div>

                    @elseif($type === 'image_text')
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            @if($bImage)
                                <img src="{{ $bImage }}" alt="{{ $bTitle }}" class="w-full rounded-3xl object-cover shadow-xl" loading="lazy">
                            @endif
                            <div>
                                <h2 class="text-2xl font-black sm:text-3xl">{{ $bTitle }}</h2>
                                @if($bSubtitle !== '')<p class="mt-2 text-sm font-black lp-accent">{{ $bSubtitle }}</p>@endif
                                @if($bBody !== '')<p class="mt-4 whitespace-pre-line text-sm leading-8 {{ $sectionMuted }}">{{ $bBody }}</p>@endif
                                @include('front.landing-pages.partials.buttons')
                            </div>
                        </div>

                    @elseif($type === 'video')
                        @include('front.landing-pages.partials.heading')
                        @if($embed = LandingPage::videoEmbedUrl($block['video_url'] ?? null))
                            <div class="mx-auto mt-8 aspect-video max-w-4xl overflow-hidden rounded-3xl shadow-2xl">
                                <iframe src="{{ $embed }}" class="h-full w-full" allowfullscreen loading="lazy" title="{{ $bTitle ?: 'Video' }}"></iframe>
                            </div>
                        @endif

                    @elseif($type === 'countdown')
                        <div class="mx-auto max-w-3xl rounded-3xl border p-8 text-center {{ $card }}">
                            <h2 class="text-2xl font-black">{{ $bTitle }}</h2>
                            @if($bBody !== '')<p class="mt-3 text-sm leading-7 {{ $muted }}">{{ $bBody }}</p>@endif
                            @if(!empty($block['countdown_at']))
                                <div class="mt-6" data-lp-countdown="{{ \Illuminate\Support\Carbon::parse($block['countdown_at'])->toIso8601String() }}"></div>
                            @endif
                            @include('front.landing-pages.partials.buttons', ['center' => true])
                        </div>

                    @elseif($type === 'coupon')
                        <div class="mx-auto max-w-2xl rounded-3xl border-2 border-dashed p-8 text-center" style="border-color: var(--lp-primary);">
                            <h2 class="text-2xl font-black">{{ $bTitle ?: 'Special discount code' }}</h2>
                            @if($bBody !== '')<p class="mt-3 text-sm leading-7 {{ $sectionMuted }}">{{ $bBody }}</p>@endif
                            @if(filled($block['coupon_code'] ?? null))
                                <div class="mt-6 inline-flex items-stretch overflow-hidden rounded-2xl" dir="ltr">
                                    <span class="px-6 py-3 font-mono text-xl font-black tracking-widest {{ $dark ? 'bg-white text-slate-950' : 'bg-slate-900 text-white' }}">{{ $block['coupon_code'] }}</span>
                                    <button type="button" class="lp-btn px-5 text-sm font-black" data-lp-copy="{{ $block['coupon_code'] }}">Copy</button>
                                </div>
                            @endif
                            @include('front.landing-pages.partials.buttons', ['center' => true])
                        </div>

                    @elseif($type === 'faq')
                        @include('front.landing-pages.partials.heading')
                        <div class="mx-auto mt-8 max-w-3xl space-y-3">
                            @foreach(LandingPage::lines($block['items_text'] ?? '') as $item)
                                <details class="group rounded-2xl border p-5 {{ $card }}">
                                    <summary class="cursor-pointer list-none text-sm font-black">{{ $item[0] }}</summary>
                                    @if(isset($item[1]))<p class="mt-3 text-sm leading-7 {{ $muted }}">{{ implode(' | ', array_slice($item, 1)) }}</p>@endif
                                </details>
                            @endforeach
                        </div>

                    @elseif($type === 'gallery')
                        @include('front.landing-pages.partials.heading')
                        <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-3">
                            @foreach(LandingPage::lines($block['items_text'] ?? '') as $item)
                                @if($src = LandingPage::safeUrl($item[0]))
                                    <img src="{{ $src }}" alt="{{ $item[1] ?? $bTitle }}" class="aspect-video w-full rounded-2xl object-cover" loading="lazy">
                                @endif
                            @endforeach
                        </div>

                    @elseif($type === 'cta')
                        <div class="relative overflow-hidden rounded-3xl p-8 text-center sm:p-12 {{ $variant === 'default' ? 'lp-band-accent' : '' }}">
                            <h2 class="text-2xl font-black sm:text-4xl">{{ $bTitle }}</h2>
                            @if($bBody !== '')<p class="mx-auto mt-4 max-w-2xl whitespace-pre-line text-sm leading-8 {{ $sectionMuted }}">{{ $bBody }}</p>@endif
                            @include('front.landing-pages.partials.buttons', ['center' => true])
                        </div>

                    @else
                        @include('front.landing-pages.partials.heading')
                        @if($bBody !== '')
                            <div class="mx-auto mt-6 max-w-3xl whitespace-pre-line text-sm leading-8 {{ $sectionMuted }}">{{ $bBody }}</div>
                        @endif
                    @endif
                </div>
            </section>
        @endforeach
    </main>
@endsection

@push('scripts')
    <script>
        (() => {
            const digits = (n) => String(n).padStart(2, '0');
            document.querySelectorAll('[data-lp-countdown]').forEach((box) => {
                const target = new Date(box.dataset.lpCountdown).getTime();
                const render = () => {
                    const left = Math.max(0, target - Date.now());
                    const parts = [[Math.floor(left / 864e5), 'days'], [Math.floor(left / 36e5) % 24, 'hours'], [Math.floor(left / 6e4) % 60, 'minutes'], [Math.floor(left / 1e3) % 60, 'seconds']];
                    box.innerHTML = '<div class="flex justify-center gap-3" dir="ltr">' + parts.map(([v, l]) =>
                        '<div class="min-w-16 rounded-2xl px-3 py-2" style="background: var(--lp-primary); color: #fff"><div class="text-2xl font-black">' + digits(v) + '</div><div class="text-[10px] font-bold opacity-80">' + l + '</div></div>'
                    ).join('') + '</div>';
                };
                render();
                setInterval(render, 1000);
            });
            document.querySelectorAll('[data-lp-copy]').forEach((button) => button.addEventListener('click', async () => {
                try { await navigator.clipboard.writeText(button.dataset.lpCopy); button.textContent = 'Copied ✓'; } catch (e) {}
            }));
        })();
    </script>
@endpush
