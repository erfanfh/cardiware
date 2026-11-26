@extends('front.identity.users.auth.master')

@push('styles')
    <style>
        @keyframes authFadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes authFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-16px) scale(1.06); }
        }

        @keyframes authRingSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes authGridMove {
            from { background-position: 0 0; }
            to { background-position: 44px 44px; }
        }

        @keyframes authStepIn {
            from { opacity: 0; transform: translateY(14px) scale(.99); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes authParticle {
            0% { transform: translateY(0) scale(1); opacity: 0; }
            12% { opacity: .8; }
            88% { opacity: .5; }
            100% { transform: translateY(-52vh) scale(.5); opacity: 0; }
        }

        /* The sign-in page is always dark and consistent */
        .auth-page {
            background:
                radial-gradient(60% 80% at 85% 0%, rgba(249, 115, 22, .10), transparent 55%),
                radial-gradient(55% 70% at 8% 100%, rgba(59, 130, 246, .09), transparent 60%),
                linear-gradient(160deg, #0b1220, #0d1220 55%, #101a2e);
        }

        .auth-panel-grid {
            background-image:
                linear-gradient(rgba(148, 163, 184, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px);
            background-size: 44px 44px;
            animation: authGridMove 16s linear infinite;
            mask-image: radial-gradient(75% 75% at 50% 40%, #000 35%, transparent 100%);
            -webkit-mask-image: radial-gradient(75% 75% at 50% 40%, #000 35%, transparent 100%);
        }

        .auth-orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(64px);
            pointer-events: none;
            animation: authFloat 7s ease-in-out infinite;
        }

        .auth-particle {
            position: absolute;
            bottom: -4vh;
            border-radius: 9999px;
            pointer-events: none;
            background: rgba(251, 146, 60, .75);
            box-shadow: 0 0 12px rgba(249, 115, 22, .75);
            animation: authParticle linear infinite;
        }

        .auth-particle.blue { background: rgba(96, 165, 250, .7); box-shadow: 0 0 12px rgba(59, 130, 246, .7); }

        .auth-card-wrap {
            position: relative;
            padding: 2px;
            border-radius: 26px;
            overflow: hidden;
            isolation: isolate;
        }

        .auth-card-wrap::before {
            content: '';
            position: absolute;
            inset: -130%;
            z-index: -1;
            background: conic-gradient(
                from 0deg,
                rgba(249, 115, 22, 0) 0deg,
                rgba(249, 115, 22, .75) 70deg,
                rgba(251, 146, 60, .25) 150deg,
                rgba(249, 115, 22, 0) 230deg,
                rgba(249, 115, 22, .45) 330deg,
                rgba(249, 115, 22, 0) 360deg
            );
            animation: authRingSpin 7s linear infinite;
        }

        .auth-entrance { animation: authFadeUp .6s ease both; }
        .auth-entrance-2 { animation: authFadeUp .6s ease .15s both; }

        .auth-step {
            animation: authStepIn .45s cubic-bezier(.16, 1, .3, 1) both;
        }

        /* Inputs and labels on dark background */
        .auth-dark-scope,
        .auth-dark-scope * {
            color-scheme: dark;
        }

        .auth-dark-scope input,
        .auth-dark-scope textarea {
            background-color: rgba(2, 6, 23, .6) !important;
            border-color: rgb(51 65 85) !important;
            color: rgb(226 232 240) !important;
        }

        .auth-dark-scope input::placeholder {
            color: rgb(100 116 139) !important;
        }

        .auth-dark-scope input:focus {
            border-color: rgba(249, 115, 22, .6) !important;
            background-color: rgba(2, 6, 23, .85) !important;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, .12) !important;
        }

        .auth-dark-scope label,
        .auth-dark-scope p,
        .auth-dark-scope span {
            color: rgb(203 213 225);
        }

        .auth-dark-scope [class*="text-slate-4"],
        .auth-dark-scope .text-slate-400 {
            color: rgb(148 163 184) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-panel-grid,
            .auth-orb,
            .auth-particle,
            .auth-card-wrap::before,
            .auth-entrance,
            .auth-entrance-2,
            .auth-step {
                animation: none;
            }
        }
    </style>
@endpush

@section('content')
    <main class="auth-page auth-dark-scope relative min-h-screen w-full overflow-hidden text-slate-100">
        {{-- Animated full-page background --}}
        <div class="auth-panel-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="auth-orb -top-24 -left-24 h-80 w-80 bg-brand-500/25" aria-hidden="true"></div>
        <div class="auth-orb -bottom-28 -right-28 h-96 w-96 bg-indigo-500/20" style="animation-delay: 1.4s" aria-hidden="true"></div>
        <div class="auth-orb top-1/3 right-1/4 h-56 w-56 bg-brand-500/15" style="animation-delay: .7s" aria-hidden="true"></div>

        <span class="auth-particle" style="right:10%; width:5px; height:5px; animation-duration:10s;" aria-hidden="true"></span>
        <span class="auth-particle blue" style="right:26%; width:4px; height:4px; animation-duration:13s; animation-delay:2s;" aria-hidden="true"></span>
        <span class="auth-particle" style="right:45%; width:6px; height:6px; animation-duration:11s; animation-delay:4s;" aria-hidden="true"></span>
        <span class="auth-particle blue" style="right:64%; width:4px; height:4px; animation-duration:14s; animation-delay:1s;" aria-hidden="true"></span>
        <span class="auth-particle" style="right:82%; width:5px; height:5px; animation-duration:12s; animation-delay:5s;" aria-hidden="true"></span>

        <div class="relative z-10 flex min-h-screen w-full">
            {{-- Intro panel (desktop) --}}
            <section class="relative hidden lg:flex flex-1 items-center justify-center overflow-hidden px-10">
                @if(get_setting('auth', 'image')->path)
                    <img
                        src="{{ get_setting('auth', 'image')?->path }}"
                        alt="auth-image"
                        class="absolute inset-0 w-full h-full object-cover object-center scale-105 opacity-55"
                    >
                @endif

                <div class="absolute inset-0 bg-gradient-to-l from-transparent via-slate-950/60 to-slate-950/85"></div>

                <div class="relative z-10 max-w-xl flex flex-col items-center gap-5 text-center">
                    <a href="{{ route('home') }}" class="auth-entrance inline-flex items-center gap-2.5 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur transition hover:bg-white/15">
                        <span class="h-2.5 w-2.5 rounded-full bg-brand-400" style="box-shadow: 0 0 14px rgba(249, 115, 22, .9)"></span>
                        <span class="text-sm font-extrabold">{{ get_setting('general', 'title') }}</span>
                    </a>

                    @if(get_setting('auth', 'label'))
                        <div class="auth-entrance rounded-2xl border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                            <div class="text-sm font-extrabold leading-8 text-white sm:text-base sm:leading-9">
                                {{ get_setting('auth', 'label') }}
                            </div>
                        </div>
                    @endif

                    @if(get_setting('auth', 'button_title'))
                        <div class="auth-entrance-2 mt-1">
                            <a href="{{ get_setting('auth', 'button_link') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-7 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-brand-500/30
                       hover:bg-brand-600 active:bg-brand-700 transition hover:-translate-y-0.5">
                                {{ get_setting('auth', 'button_title') }}
                                <span class="[&_svg]:h-4 [&_svg]:w-4">
                                    <x-front.arrow-right-02/>
                                </span>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Sign-in card --}}
            <aside class="relative flex w-full items-center justify-center px-4 py-10 md:px-10 lg:w-[42%]">
                <div class="w-full max-w-lg">
                    <div class="mb-4 flex justify-end">
                        <a
                            href="{{ route('home') }}"
                            class="group inline-flex items-center gap-1.5 rounded-lg px-1 py-1 text-[0.7rem] font-medium text-slate-400 transition-colors hover:text-brand-400 focus:outline-none"
                        >
                            <span class="opacity-80 transition-transform duration-200 group-hover:-translate-x-0.5 [&_svg]:h-3.5 [&_svg]:w-3.5">
                                @include('icons.home-12')
                            </span>
                            <span>Back to home</span>
                        </a>
                    </div>

                    <div class="auth-card-wrap">
                        <div class="auth-entrance rounded-[24px] bg-slate-900/85 p-8 shadow-2xl backdrop-blur-xl [&_h2]:text-white [&_p]:text-slate-300 [&_label]:text-slate-300 [&_input]:text-slate-100">
                            <livewire:front.identity.user.auth.auth-flow/>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>
@endsection
