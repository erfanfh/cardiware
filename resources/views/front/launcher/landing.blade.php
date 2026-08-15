@extends('front.master')

@section('head')
    <title>دانلود MW Launcher | مودینگ‌وی</title>
    <meta name="description" content="دانلود لانچر رسمی مودینگ‌وی برای نصب محصولات، مدیریت مادها، دریافت آپدیت و پشتیبانی آنلاین.">
@endsection

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <section class="overflow-hidden rounded-3xl border border-orange-200 bg-slate-950 text-white shadow-2xl dark:border-orange-900/60">
            <div class="grid gap-10 px-6 py-10 sm:px-10 lg:grid-cols-2 lg:items-center lg:px-16 lg:py-16">
                <div>
                    <span class="inline-flex rounded-full border border-orange-400/40 bg-orange-500/10 px-4 py-2 text-xs font-extrabold text-orange-300">
                        لانچر رسمی Moddingway
                    </span>
                    <h1 class="mt-5 text-3xl font-black leading-tight sm:text-5xl">همه محصولات و مادها، در یک لانچر</h1>
                    <p class="mt-5 max-w-xl text-sm leading-8 text-slate-300 sm:text-base">
                        MW Launcher دانلودهای حجیم را مدیریت می‌کند، نصب و آپدیت محصولات را انجام می‌دهد و ابزار مدیریت مادها و پشتیبانی آنلاین را در اختیار شما می‌گذارد.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if(filled($downloadUrl))
                            <a href="{{ $downloadUrl }}"
                               class="inline-flex h-12 items-center justify-center rounded-xl bg-orange-500 px-7 text-sm font-black text-white transition hover:bg-orange-400">
                                دانلود MW Launcher برای ویندوز
                            </a>
                        @else
                            <span class="inline-flex h-12 items-center justify-center rounded-xl bg-slate-800 px-7 text-sm font-bold text-slate-400">
                                لینک دانلود به‌زودی فعال می‌شود
                            </span>
                        @endif
                        <a href="{{ url('/') }}"
                           class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-700 px-7 text-sm font-extrabold transition hover:border-orange-400 hover:text-orange-300">
                            بازگشت به فروشگاه
                        </a>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach([
                        ['دانلود مطمئن', 'توقف، ادامه و بازیابی دانلودهای حجیم'],
                        ['مدیریت مادها', 'فعال‌سازی و غیرفعال‌سازی مادها از داخل لانچر'],
                        ['آپدیت خودکار', 'دریافت نسخه‌های تازه محصولات و خود لانچر'],
                        ['پشتیبانی آنلاین', 'دسترسی مستقیم خریداران به پشتیبانی مودینگ‌وی'],
                    ] as [$featureTitle, $featureText])
                        <article class="border border-slate-800 bg-slate-900/80 p-5">
                            <div class="h-1 w-10 bg-orange-500"></div>
                            <h2 class="mt-4 text-base font-black">{{ $featureTitle }}</h2>
                            <p class="mt-2 text-xs leading-6 text-slate-400">{{ $featureText }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach([
                ['۱', 'لانچر را دانلود و نصب کنید'],
                ['۲', 'با حساب فروشگاه وارد شوید'],
                ['۳', 'محصول خریداری‌شده را دانلود و نصب کنید'],
            ] as [$number, $step])
                <div class="border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-2xl font-black text-orange-500">{{ $number }}</span>
                    <p class="mt-2 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ $step }}</p>
                </div>
            @endforeach
        </section>
    </main>
@endsection
