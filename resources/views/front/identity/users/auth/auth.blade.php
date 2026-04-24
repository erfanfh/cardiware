@extends('front.identity.users.auth.master')

@section('content')
    <main class="min-h-screen flex">
        <section class="relative hidden lg:block flex-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/75 via-slate-950/45 to-slate-950/15"></div>

            <div class="absolute -top-24 -left-24 h-80 w-80 rounded-[90px] bg-orange-500/25 blur-3xl"></div>
            <div class="absolute -bottom-28 -right-28 h-96 w-96 rounded-[100px] bg-orange-100/35 blur-3xl"></div>
            <div class="absolute top-10 right-10 h-3 w-3 rounded-full bg-orange-500"></div>

            <div class="relative h-full flex items-center justify-center px-6">
                <div class="w-full max-w-2xl text-center">
                    @if(get_setting('auth', 'image')->path)
                        <div class="flex justify-center mx-auto mb-7 relative w-[340px] max-w-full">
                            <img alt="auth-image" src="{{ get_setting('auth', 'image')?->path }}">
                        </div>
                    @endif

                    <div class="mx-auto max-w-xl">
                        @if(get_setting('auth', 'label'))
                            <div
                                class="inline-flex items-center gap-2 rounded-2xl bg-white/10 text-white/90 backdrop-blur px-3 py-2 text-xs font-bold mb-4">
                                <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                                <span id="heroTag">{{ get_setting('auth', 'label') }}</span>
                            </div>
                        @endif

                        @if(get_setting('auth', 'button_title'))
                            <div class="mt-7">
                                <a id="heroCta" href="{{ get_setting('auth', 'button_link') }}"
                                   class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-8 py-3 text-sm font-extrabold text-white shadow-xl
                            hover:bg-orange-600 active:bg-orange-700 transition">
                                    {{ get_setting('auth', 'button_title') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <aside class="relative bg-slate-50 w-full lg:w-2/5">
            <div class="w-full h-full flex items-center justify-center px-4 md:px-10">
                <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200 p-8 my-4">
                    <livewire:front.identity.user.auth.auth-flow/>
                </div>
            </div>
        </aside>
    </main>
@endsection
