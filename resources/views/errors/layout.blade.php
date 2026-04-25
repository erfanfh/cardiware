<!doctype html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <meta name="robots" content="noindex, follow">
    <meta name="description" content="صفحه‌ای که دنبال آن هستید پیدا نشد.">

    <x-favicons/>
    @vite(['resources/css/app.css', 'resources/css/front/main.css'])

    <style>
        @keyframes floatSoft {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-12px);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes rotateSlow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes blink {
            0%, 92%, 100% {
                transform: scaleY(1);
            }
            95% {
                transform: scaleY(.12);
            }
        }

        .animate-float-soft {
            animation: floatSoft 4s ease-in-out infinite;
        }

        .animate-fade-up {
            animation: fadeUp .65s ease both;
        }

        .animate-rotate-slow {
            animation: rotateSlow 18s linear infinite;
            transform-origin: center;
        }

        .animate-blink {
            animation: blink 3s ease-in-out infinite;
            transform-origin: center;
        }

        .delay-100 {
            animation-delay: .1s;
        }

        .delay-200 {
            animation-delay: .2s;
        }

        .delay-300 {
            animation-delay: .3s;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">


<main class="relative min-h-screen overflow-hidden px-4">

    <div
        class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-100 blur-3xl dark:bg-brand-500/10"></div>
    <div
        class="pointer-events-none absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-slate-200/80 blur-3xl dark:bg-slate-800/70"></div>

    <section class="relative mx-auto flex min-h-screen max-w-7xl items-center justify-center">
        <div class="w-full max-w-3xl text-center py-4">

            <div
                class="animate-fade-up mx-auto mb-5 inline-flex items-center gap-2 rounded-2xl border border-brand-100 bg-brand-50 px-3 py-2 text-[11px] font-extrabold text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                خطای @yield('code')
            </div>

            <div class="animate-fade-up delay-100 mx-auto mb-6 flex justify-center">
                <div
                    class="relative grid h-56 w-56 place-items-center rounded-[2rem] border border-slate-200 bg-white shadow-xl sm:h-72 sm:w-72 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30">

                    <div class="absolute -right-4 -top-4 h-16 w-16 rounded-3xl bg-brand-100 dark:bg-brand-500/10"></div>
                    <div class="absolute -bottom-4 -left-4 h-14 w-14 rounded-full bg-slate-100 dark:bg-slate-800"></div>

                    <svg class="relative h-44 w-44 animate-float-soft sm:h-56 sm:w-56" viewBox="0 0 260 260" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <circle cx="130" cy="130" r="96" fill="#FFF7ED"/>
                        <circle class="animate-rotate-slow" cx="130" cy="130" r="104" stroke="#FED7AA" stroke-width="8"
                                stroke-dasharray="18 18"/>

                        <rect x="70" y="82" width="120" height="92" rx="24" fill="white" stroke="#CBD5E1"
                              stroke-width="4"/>
                        <path d="M92 82V72C92 59.8497 101.85 50 114 50H146C158.15 50 168 59.8497 168 72V82"
                              stroke="#F97316" stroke-width="8" stroke-linecap="round"/>

                        <circle cx="109" cy="121" r="8" fill="#0F172A" class="animate-blink"/>
                        <circle cx="151" cy="121" r="8" fill="#0F172A" class="animate-blink"/>

                        <path d="M109 151C121 141 139 141 151 151" stroke="#EA580C" stroke-width="7"
                              stroke-linecap="round"/>

                        <path d="M65 183L50 202" stroke="#F97316" stroke-width="7" stroke-linecap="round"/>
                        <path d="M195 183L210 202" stroke="#F97316" stroke-width="7" stroke-linecap="round"/>

                        <circle cx="52" cy="71" r="8" fill="#FB923C"/>
                        <circle cx="206" cy="64" r="6" fill="#FDBA74"/>
                        <circle cx="213" cy="160" r="9" fill="#FED7AA"/>
                    </svg>

                    <div
                        class="absolute bottom-5 rounded-2xl bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        @yield('label')
                    </div>
                </div>
            </div>

            <h1 class="animate-fade-up delay-200 text-2xl font-black leading-tight text-slate-900 sm:text-3xl dark:text-white">
                @yield('box-title')
            </h1>

            <p class="animate-fade-up delay-300 mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-600 sm:text-base dark:text-slate-300">
                @yield('box-subtitle')
            </p>

            <div class="animate-fade-up delay-300 mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-lg transition hover:bg-brand-600 active:bg-brand-700 sm:w-auto dark:shadow-black/20 dark:hover:bg-brand-400">
                    بازگشت به صفحه اصلی
                    <span>←</span>
                </a>

                <a href="{{ route('posts.list') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-brand-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">
                    مشاهده نوشته‌ها
                </a>
            </div>

        </div>
    </section>

</main>

</body>
</html>
