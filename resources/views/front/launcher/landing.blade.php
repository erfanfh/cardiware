@extends('front.master')

@section('head')
    <title>{{ get_setting('launcher', 'subtitle', 'Download MW Launcher') }} | {{ get_setting('general', 'title') }}</title>
    <meta name="description"
          content="Download official launcher Cardiware Install products, manage mods, receive updates and get online support.">
@endsection

@push('styles')
    <style>
        #footer {
            display: none;
        }

        #header {
            display: none;
        }

        @keyframes mwLFadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes mwLSoftGlow {
            0%, 100% { opacity: .5; transform: scale(1); }
            50% { opacity: .95; transform: scale(1.07); }
        }

        @keyframes mwLRingPulse {
            0% { box-shadow: 0 0 0 0 rgba(249, 115, 22, .55); }
            100% { box-shadow: 0 0 0 22px rgba(249, 115, 22, 0); }
        }

        @keyframes mwLCtaSheen {
            0%, 55% { transform: translateX(-180%) rotate(15deg); opacity: 0; }
            65% { opacity: .75; }
            85%, 100% { transform: translateX(420%) rotate(15deg); opacity: 0; }
        }

        @keyframes mwLMatrixTitle {
            0%, 100% { text-shadow: 0 0 18px rgba(249, 115, 22, .35); }
            50% { text-shadow: 0 0 34px rgba(249, 115, 22, .65); }
        }

        .mwl-fade-up { animation: mwLFadeUp .7s ease both; }
        .mwl-delay-100 { animation-delay: .12s; }
        .mwl-delay-200 { animation-delay: .24s; }
        .mwl-delay-300 { animation-delay: .36s; }

        .mwl-orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(70px);
            pointer-events: none;
            animation: mwLSoftGlow 6s ease-in-out infinite;
        }

        .mwl-cta-ring { position: relative; }

        .mwl-cta-ring::before,
        .mwl-cta-ring::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            pointer-events: none;
            animation: mwLRingPulse 2.4s ease-out infinite;
        }

        .mwl-cta-ring::after { animation-delay: 1.2s; }

        .mwl-cta-sheen {
            position: absolute;
            inset: 0;
            overflow: hidden;
            border-radius: inherit;
            pointer-events: none;
        }

        .mwl-cta-sheen::after {
            content: '';
            position: absolute;
            top: -60%;
            bottom: -60%;
            left: 0;
            width: 28%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .45), transparent);
            transform: translateX(-180%) rotate(15deg);
            animation: mwLCtaSheen 3.8s ease-in-out infinite;
        }

        .mwl-title-glow {
            animation: mwLMatrixTitle 3.2s ease-in-out infinite;
        }

        .mwl-feature {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(15, 23, 42, .35);
            backdrop-filter: blur(6px);
            padding: 1rem .6rem;
            color: #fff;
            transition: border-color .25s ease, transform .25s ease, box-shadow .25s ease;
        }

        .mwl-feature:hover {
            border-color: rgba(249, 115, 22, .45);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(249, 115, 22, .12);
        }

        .mwl-feature-icon { width: 24px; height: 24px; color: #fb923c; flex-shrink: 0; }
        .mwl-feature-label { font-size: 11.5px; font-weight: 700; line-height: 1.6; color: #fff; }

        @media (min-width: 640px) {
            .mwl-feature-icon { width: 26px; height: 26px; }
            .mwl-feature-label { font-size: 12.5px; }
        }

        .mwl-preview {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .16);
            transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease;
        }

        .mwl-preview img {
            width: 100%;
            aspect-ratio: 16 / 10;
            object-fit: cover;
            display: block;
        }

        .mwl-preview::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(2, 6, 23, .72), transparent 55%);
        }

        .mwl-preview figcaption {
            position: absolute;
            z-index: 2;
            inset-inline: 12px;
            bottom: 10px;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
        }

        .mwl-preview:hover {
            transform: translateY(-4px) scale(1.015);
            border-color: rgba(249, 115, 22, .45);
            box-shadow: 0 18px 40px rgba(0, 0, 0, .45);
        }

        .mwl-step {
            position: relative;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(15, 23, 42, .45);
            backdrop-filter: blur(6px);
            padding: 14px 16px;
        }

        .mwl-step-num {
            width: 34px;
            height: 34px;
            flex: none;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(249, 115, 22, .16);
            border: 1px solid rgba(249, 115, 22, .4);
            color: #fb923c;
            font-size: 14px;
            font-weight: 900;
        }

        .mwl-step-title { font-size: 13.5px; font-weight: 900; color: #fff; }
        .mwl-step-desc { margin-top: 3px; font-size: 12px; line-height: 1.9; color: rgba(255, 255, 255, .65); }

        .mwl-section-title {
            font-size: 15px;
            font-weight: 900;
            color: #fff;
        }

        .mwl-section-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, .15);
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
        }

        @keyframes mwLBeamSweep {
            0%, 100% { transform: rotate(-14deg) translateX(-6%); opacity: .25; }
            50% { transform: rotate(8deg) translateX(6%); opacity: .6; }
        }

        @keyframes mwLParticle {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            12% { opacity: .85; }
            88% { opacity: .6; }
            100% { transform: translateY(-46vh) scale(.55); opacity: 0; }
        }

        .mwl-beam {
            position: absolute;
            top: -22%;
            width: 26vw;
            height: 150%;
            pointer-events: none;
            background: linear-gradient(to bottom, rgba(249, 115, 22, .16), rgba(249, 115, 22, .05) 55%, transparent);
            filter: blur(26px);
            transform-origin: top center;
            animation: mwLBeamSweep 9s ease-in-out infinite;
        }

        .mwl-beam-1 { right: 4%; }
        .mwl-beam-2 { left: 6%; animation-delay: 3.2s; animation-duration: 11s; background: linear-gradient(to bottom, rgba(56, 130, 246, .14), rgba(56, 130, 246, .04) 55%, transparent); }

        .mwl-particle {
            position: absolute;
            bottom: -4vh;
            border-radius: 9999px;
            pointer-events: none;
            background: rgba(251, 146, 60, .8);
            box-shadow: 0 0 12px rgba(249, 115, 22, .8);
            animation: mwLParticle linear infinite;
        }

        .mwl-particle.blue { background: rgba(96, 165, 250, .75); box-shadow: 0 0 12px rgba(59, 130, 246, .8); }

        @media (prefers-reduced-motion: reduce) {
            #mwl-matrix { display: none; }

            .mwl-fade-up,
            .mwl-orb,
            .mwl-cta-ring::before,
            .mwl-cta-ring::after,
            .mwl-cta-sheen::after,
            .mwl-title-glow,
            .mwl-beam,
            .mwl-particle {
                animation: none !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        /*
         * Hero background: light grid «Playing field» with a slow light sweep.
         *
         * Replaces previous matrix rain effect (falling characters + click ripple) which
         * was busy and heavy on mobile. This version is lightweight: only a few lines
         * of grid with a slow light band, no click response.
         * If the user prefers-reduced-motion has it enabled, draw a static frame and stop.
         */
        (function () {
            var canvas = document.getElementById('mwl-matrix');

            if (!canvas) return;

            var ctx = canvas.getContext('2d');
            if (!ctx) return;

            var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var speed = Math.max(4, Math.min(60, parseInt('{!! (string) (int) get_setting('launcher', 'matrix_speed', 24) !!}', 10) || 24));

            var cell = 56;
            var brand = '249, 115, 22';
            var cool = '96, 165, 250';
            var lastFrame = 0;
            var frameInterval = 1000 / speed;
            var phase = 0;
            var width = 0;
            var height = 0;

            function resize() {
                var ratio = Math.min(window.devicePixelRatio || 1, 2);
                width = canvas.offsetWidth;
                height = canvas.offsetHeight;
                canvas.width = Math.max(1, Math.round(width * ratio));
                canvas.height = Math.max(1, Math.round(height * ratio));
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                drawGrid(reducedMotion ? 0.5 : phase);
            }

            window.addEventListener('resize', resize, { passive: true });
            resize();

            function drawGrid(glowProgress) {
                ctx.clearRect(0, 0, width, height);

                // Light band: moves from bottom right to top left.
                var bandWidth = Math.max(width, height) * 0.45;
                var travel = width + height + bandWidth * 2;
                var bandCenter = travel * glowProgress - bandWidth;

                for (var x = 0; x <= width + cell; x += cell) {
                    for (var y = 0; y <= height + cell; y += cell) {
                        var diagonal = (width - x) + y;
                        var distance = Math.abs(diagonal - bandCenter);
                        var intensity = Math.max(0, 1 - distance / bandWidth);

                        var crossX = x === 0 || Math.abs(x - width) < 1;
                        var crossY = y === 0 || Math.abs(y - height) < 1;

                        // Grid lines: very faint base + Halo along the light band.
                        ctx.strokeStyle = 'rgba(148, 163, 184, ' + (0.05 + intensity * 0.10).toFixed(3) + ')';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        if (!crossX) {
                            ctx.moveTo(x, y);
                            ctx.lineTo(x, Math.min(y + cell, height));
                        }
                        if (!crossY) {
                            ctx.moveTo(x, y);
                            ctx.lineTo(Math.min(x + cell, width), y);
                        }
                        ctx.stroke();

                        // Intersection: brighter and larger along the light path.
                        var dotAlpha = 0.10 + intensity * 0.55;
                        var dotRadius = 1 + intensity * 1.8;
                        var color = intensity > 0.55 ? brand : cool;

                        ctx.fillStyle = 'rgba(' + color + ', ' + dotAlpha.toFixed(3) + ')';
                        ctx.beginPath();
                        ctx.arc(x, y, dotRadius, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }
            }

            function draw(timestamp) {
                if (timestamp - lastFrame < frameInterval) return;
                lastFrame = timestamp;

                phase += 0.0016;
                if (phase > 1) phase -= 1;

                drawGrid(phase);
            }

            if (reducedMotion) {
                drawGrid(0.5);
                return;
            }

            // Pause animation when tab is hidden to CPU/save battery.
            var visible = !document.hidden;
            document.addEventListener('visibilitychange', function () {
                visible = !document.hidden;
                if (visible) lastFrame = 0;
            });

            function loop(timestamp) {
                if (visible) draw(timestamp);
                requestAnimationFrame(loop);
            }

            requestAnimationFrame(loop);
        })();
    </script>
@endpush

@section('content')
    @php
        $launcherFeatures = collect(get_setting('launcher', 'features') ?? [])->filter(fn($f) => filled($f['label'] ?? null))->values();
        $launcherPreviews = collect(get_setting('launcher', 'previews') ?? [])->filter(fn($p) => filled($p['image']['path'] ?? null))->values();
        $launcherSteps = collect(get_setting('launcher', 'tutorial_steps') ?? [])->filter(fn($s) => filled($s['title'] ?? null))->values();
        $launcherMatrix = (bool) get_setting('launcher', 'matrix_enabled', 1);
    @endphp

    <div class="min-h-screen w-full overflow-hidden bg-slate-950 text-slate-100">
        <div class="relative min-h-screen w-full">

            <!-- Ambient background -->
            <div class="pointer-events-none absolute inset-0 z-0 bg-slate-950">
                <div class="mwl-orb -top-24 -right-24 h-80 w-80 bg-brand-500/20"></div>
                <div class="mwl-orb -bottom-24 -left-24 h-96 w-96 bg-indigo-500/15" style="animation-delay: 1.4s"></div>

                {{-- Animated stadium lights --}}
                <span class="mwl-beam mwl-beam-1" aria-hidden="true"></span>
                <span class="mwl-beam mwl-beam-2" aria-hidden="true"></span>

                {{-- Floating particles --}}
                <span class="mwl-particle" style="right:12%; width:5px; height:5px; animation-duration:9s;" aria-hidden="true"></span>
                <span class="mwl-particle blue" style="right:28%; width:4px; height:4px; animation-duration:12s; animation-delay:2s;" aria-hidden="true"></span>
                <span class="mwl-particle" style="right:47%; width:6px; height:6px; animation-duration:10s; animation-delay:4s;" aria-hidden="true"></span>
                <span class="mwl-particle blue" style="right:63%; width:4px; height:4px; animation-duration:13s; animation-delay:1s;" aria-hidden="true"></span>
                <span class="mwl-particle" style="right:78%; width:5px; height:5px; animation-duration:11s; animation-delay:5s;" aria-hidden="true"></span>
                <span class="mwl-particle blue" style="right:90%; width:3px; height:3px; animation-duration:14s; animation-delay:3s;" aria-hidden="true"></span>
            </div>

            @if($launcherMatrix)
                <!-- Matrix rain (Cardiware LAUNCHER) -->
                <canvas id="mwl-matrix" class="fixed inset-0 z-0 h-full w-full opacity-70"></canvas>
            @endif

            <!-- Content -->
            <main class="relative z-10 flex w-full flex-col items-center justify-center gap-4 px-4 py-12 text-center sm:gap-5 sm:px-6">

                @if(get_setting('launcher', 'badge_text'))
                    <span class="mwl-section-badge mwl-fade-up">
                        <span class="h-2 w-2 rounded-full bg-brand-400" style="box-shadow: 0 0 12px rgba(249,115,22,.9)"></span>
                        {{ get_setting('launcher', 'badge_text') }}
                    </span>
                @endif

                <h1 class="mwl-title-glow mwl-fade-up mwl-delay-100 m-0 select-none text-xl font-black leading-tight text-white sm:text-2xl md:text-3xl lg:text-4xl">
                    {{ get_setting('launcher', 'title', 'Dedicated launcher Cardiware') }}
                </h1>

                <p class="mwl-fade-up mwl-delay-200 m-0 select-none text-base font-extrabold text-brand-400 sm:text-lg md:text-xl">
                    {{ get_setting('launcher', 'subtitle', 'Download and install MW Launcher') }}
                </p>

                @if(get_setting('launcher', 'description'))
                    <p class="mwl-fade-up mwl-delay-200 m-0 max-w-xl select-none text-xs leading-7 text-slate-300 sm:text-sm md:text-base">
                        {{ get_setting('launcher', 'description') }}
                    </p>
                @endif

                <div class="mwl-fade-up mwl-delay-300 mt-3 flex flex-wrap items-center justify-center gap-3">
                    @if(filled($downloadUrl))
                        <a href="{{ $downloadUrl }}"
                           class="mwl-cta-ring relative inline-flex items-center justify-center gap-2 rounded-full bg-brand-500 px-7 py-3 text-sm font-extrabold text-white shadow-lg shadow-brand-500/30 transition hover:bg-brand-600 active:bg-brand-700 sm:px-8 sm:py-3.5 sm:text-base">
                            <span class="relative z-10">{{ get_setting('launcher', 'download_button_text', 'Download Windows launcher') }}</span>
                            <span class="relative z-10" aria-hidden="true">↓</span>
                            <span class="mwl-cta-sheen" aria-hidden="true"></span>
                        </a>
                    @else
                        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-slate-600 bg-slate-800/80 px-7 py-3 text-sm font-bold text-slate-400 sm:px-8 sm:py-3.5 sm:text-base">
                            Download link is not active yet
                        </span>
                    @endif

                    <a href="{{ url('/') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-600 bg-slate-900/70 px-6 py-3 text-sm font-bold text-slate-200 transition hover:border-brand-500 hover:text-brand-300 sm:px-7 sm:py-3.5 sm:text-base">
                        {{ get_setting('launcher', 'back_button_text', 'Back to website') }}
                        <span aria-hidden="true">←</span>
                    </a>
                </div>

                @if($launcherFeatures->isNotEmpty())
                    <div class="mwl-fade-up mwl-delay-300 mt-6 grid w-full max-w-5xl grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-6">
                        @foreach($launcherFeatures->take(6) as $feature)
                            <div class="mwl-feature">
                                <svg class="mwl-feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5z"/>
                                </svg>
                                <span class="mwl-feature-label">{{ $feature['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($launcherPreviews->isNotEmpty())
                    <section class="mt-12 w-full max-w-5xl">
                        <div class="mb-4 flex items-center justify-center gap-3">
                            <span class="h-px w-10 bg-gradient-to-l from-transparent to-brand-500/60"></span>
                            <h2 class="mwl-section-title">{{ get_setting('launcher', 'previews_title', 'A look inside the launcher') }}</h2>
                            <span class="h-px w-10 bg-gradient-to-r from-transparent to-brand-500/60"></span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($launcherPreviews->take(6) as $preview)
                                <figure
                                    class="mwl-preview cursor-pointer"
                                    data-lightbox="launcher-preview"
                                    data-src="{{ $preview['image']['path'] }}"
                                    data-title="{{ $preview['title'] ?? '' }}"
                                    role="button"
                                    tabindex="0"
                                    aria-label="{{ filled($preview['title'] ?? null) ? $preview['title'] : 'View image' }}"
                                >
                                    <img src="{{ $preview['image']['path'] }}" alt="{{ $preview['title'] ?? '' }}" loading="lazy">
                                    @if(filled($preview['title'] ?? null))
                                        <figcaption>{{ $preview['title'] }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($launcherSteps->isNotEmpty() || filled(get_setting('launcher', 'tutorial_video_url')))
                    <section class="mt-12 w-full max-w-4xl">
                        <div class="mb-4 flex items-center justify-center gap-3">
                            <span class="h-px w-10 bg-gradient-to-l from-transparent to-brand-500/60"></span>
                            <h2 class="mwl-section-title">{{ get_setting('launcher', 'tutorial_title', 'How do I get started?') }}</h2>
                            <span class="h-px w-10 bg-gradient-to-r from-transparent to-brand-500/60"></span>
                        </div>

                        @if(get_setting('launcher', 'tutorial_description'))
                            <p class="mx-auto mb-6 max-w-xl text-xs leading-7 text-slate-300 sm:text-sm">
                                {{ get_setting('launcher', 'tutorial_description') }}
                            </p>
                        @endif

                        @if(filled(get_setting('launcher', 'tutorial_video_url')))
                            <div class="mx-auto mb-6 aspect-video w-full max-w-2xl overflow-hidden rounded-2xl border border-white/15 shadow-2xl shadow-black/40">
                                <iframe src="{{ get_setting('launcher', 'tutorial_video_url') }}"
                                        title="Launcher tutorial"
                                        class="h-full w-full"
                                        frameborder="0"
                                        allowfullscreen
                                        loading="lazy"></iframe>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach($launcherSteps->take(3) as $index => $step)
                                <div class="mwl-step">
                                    <span class="mwl-step-num">{{ $index + 1 }}</span>
                                    <div class="min-w-0 text-start">
                                        <div class="mwl-step-title">{{ $step['title'] }}</div>
                                        @if(filled($step['description'] ?? null))
                                            <div class="mwl-step-desc">{{ $step['description'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(filled(get_setting('launcher', 'tutorial_button_text')) && filled(get_setting('launcher', 'tutorial_button_url')))
                            <a href="{{ get_setting('launcher', 'tutorial_button_url') }}"
                               class="mt-6 inline-flex items-center justify-center gap-2 rounded-2xl border border-brand-500/40 bg-brand-500/10 px-6 py-3 text-sm font-extrabold text-brand-300 transition hover:bg-brand-500/20">
                                {{ get_setting('launcher', 'tutorial_button_text') }}
                                <span aria-hidden="true">←</span>
                            </a>
                        @endif
                    </section>
                @endif

            </main>
        </div>
    </div>
@endsection
