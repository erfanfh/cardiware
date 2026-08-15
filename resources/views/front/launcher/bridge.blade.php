@extends('front.master')

@section('head')
    <title>{{ $title }} | MW Launcher</title>
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('content')
    <main class="mx-auto flex min-h-[60vh] max-w-3xl items-center justify-center px-4 py-12">
        <section class="w-full border border-orange-200 bg-white p-6 text-center shadow-xl sm:p-10 dark:border-orange-900/60 dark:bg-slate-900">
            <div class="mx-auto grid h-16 w-16 place-items-center bg-orange-500 text-xl font-black text-white">MW</div>
            <h1 class="mt-6 text-2xl font-black text-slate-900 dark:text-white">{{ $title }}</h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $description }}</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a id="open-launcher" href="{{ $protocolUrl }}"
                   class="inline-flex h-12 items-center justify-center bg-orange-500 px-7 text-sm font-black text-white transition hover:bg-orange-400">
                    باز کردن در MW Launcher
                </a>
                <a href="{{ route('launcher.landing') }}"
                   class="inline-flex h-12 items-center justify-center border border-slate-300 px-7 text-sm font-extrabold text-slate-700 transition hover:border-orange-400 dark:border-slate-700 dark:text-slate-200">
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
