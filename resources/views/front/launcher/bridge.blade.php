@extends('front.master')

@section('head')
    <title>{{ $title }} | MW Launcher</title>
    <meta name="robots" content="noindex,nofollow">
@endsection

@push('styles')
    <style>
        @keyframes bridgeBorderGlow {
            0%, 100% {
                box-shadow: 0 0 0 1px rgba(249, 115, 22, .35), 0 0 18px 2px rgba(249, 115, 22, .12);
            }
            50% {
                box-shadow: 0 0 0 1px rgba(249, 115, 22, .8), 0 0 34px 6px rgba(249, 115, 22, .32);
            }
        }

        .bridge-card {
            animation: bridgeBorderGlow 2.6s ease-in-out infinite;
        }

        @keyframes bridgeCtaSheen {
            0%, 55% {
                transform: translateX(-180%) rotate(15deg);
                opacity: 0;
            }
            65% {
                opacity: .75;
            }
            85%, 100% {
                transform: translateX(420%) rotate(15deg);
                opacity: 0;
            }
        }

        .bridge-cta-sheen {
            position: absolute;
            inset: 0;
            overflow: hidden;
            border-radius: inherit;
            pointer-events: none;
        }

        .bridge-cta-sheen::after {
            content: '';
            position: absolute;
            top: -60%;
            bottom: -60%;
            left: 0;
            width: 28%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .55), transparent);
            transform: translateX(-180%) rotate(15deg);
            animation: bridgeCtaSheen 3.8s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .bridge-card {
                animation: none;
            }

            .bridge-cta-sheen::after {
                animation: none;
            }
        }
    </style>
@endpush

@section('content')
    <main class="mx-auto flex min-h-[60vh] max-w-3xl items-center justify-center px-4 py-12">
        <section
            class="bridge-card w-full rounded-3xl border border-orange-200 bg-white p-6 text-center shadow-xl sm:p-10 dark:border-orange-900/60 dark:bg-slate-900">
            <img src="{{ asset('images/mw-launcher-icon.png') }}" alt="لانچر Cardiware"
                 class="mx-auto h-16 w-16 select-none object-contain">
            <h1 class="mt-6 text-2xl font-black text-slate-900 dark:text-white">{{ $title }}</h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $description }}</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a id="open-launcher" href="{{ $protocolUrl }}"
                   class="relative inline-flex h-12 items-center justify-center overflow-hidden rounded-2xl bg-orange-500 px-7 text-sm font-black text-white transition hover:bg-orange-400">
                    <span class="bridge-cta-sheen" aria-hidden="true"></span>
                    باز کردن در MW Launcher
                </a>
                <a href="{{ route('launcher.landing') }}"
                   class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-300 px-7 text-sm font-extrabold text-slate-700 transition hover:border-orange-400 dark:border-slate-700 dark:text-slate-200">
                    لانچر را ندارید؟ دانلود کنید
                </a>
            </div>

            @if(filled($downloadUrl))
                <p class="mt-5 text-xs text-slate-500">اگر پنجره لانچر باز نشد، ابتدا آن را <a class="font-bold text-orange-500" href="{{ $downloadUrl }}">دانلود و نصب</a> کنید.</p>
            @endif
        </section>
    </main>
@endsection

@if($autoOpen)
    @push('scripts')
        <script>
            window.setTimeout(function () {
                window.location.href = @json($protocolUrl);
            }, 500);
        </script>
    @endpush
@endif
