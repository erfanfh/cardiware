@extends('front.identity.users.auth.master')

@section('content')
    <main class="min-h-screen flex">
        <section class="relative hidden lg:block flex-1 overflow-hidden">

            @if(get_setting('auth', 'image')->path)
                <img
                    src="{{ get_setting('auth', 'image')?->path }}"
                    alt="auth-image"
                    class="absolute inset-0 w-full h-full object-cover object-center scale-105"
                >
            @endif

            {{-- GRADIENT --}}
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/80 via-slate-950/60 to-slate-950/20"></div>

            {{-- DECOR --}}
            <div class="absolute -top-24 -left-24 h-80 w-80 rounded-[90px] bg-orange-500/25 blur-3xl"></div>
            <div
                class="absolute -bottom-28 -right-28 h-96 w-96 rounded-[100px] bg-orange-100/35 blur-3xl dark:bg-orange-500/10"></div>

            {{-- CONTENT --}}
            <div class="relative z-10 h-full flex items-center justify-center px-10">
                <div class="max-w-xl text-white flex flex-col items-center  ">

                    @if(get_setting('auth', 'label'))
                        <div
                            class="inline-flex items-center gap-2 rounded-xl bg-white/10 backdrop-blur px-4 py-2 text-xs font-bold mb-5">
                            <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                            <span>{{ get_setting('auth', 'label') }}</span>
                        </div>
                    @endif

                    @if(get_setting('auth', 'button_title'))
                        <div>
                            <a href="{{ get_setting('auth', 'button_link') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-7 py-3 text-sm font-bold text-white shadow-lg
                       hover:bg-orange-600 active:bg-orange-700 transition">
                                {{ get_setting('auth', 'button_title') }}
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </section>

        <aside class="relative bg-slate-50 w-full lg:w-2/5 dark:bg-slate-950">
            <div class="w-full h-full flex items-center justify-center px-4 md:px-10">
                <div
                    class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200 p-8 my-4 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30">
                    <livewire:front.identity.user.auth.auth-flow/>
                </div>
            </div>
        </aside>
    </main>
@endsection
