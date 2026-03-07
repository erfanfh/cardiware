@extends('front.identity.users.auth.master')

@section('title', 'فراموشی رمز عبور')

@section('content')
    <main class="min-h-screen flex">

        <section class="relative flex-1 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-l from-slate-950/75 via-slate-950/45 to-slate-950/15"></div>

            <div class="absolute -top-24 -left-24 h-80 w-80 rounded-[90px] bg-orange-500/25 blur-3xl"></div>
            <div class="absolute -bottom-28 -right-28 h-96 w-96 rounded-[100px] bg-orange-100/35 blur-3xl"></div>
            <div class="absolute top-10 right-10 h-3 w-3 rounded-full bg-orange-500"></div>

            <div class="relative h-full flex items-center justify-center px-6">
                <div class="w-full max-w-2xl text-center">
                    <div class="mx-auto mb-7 relative w-[340px] max-w-full">
                        <svg viewBox="0 0 520 260" class="w-full drop-shadow-[0_20px_40px_rgba(0,0,0,0.35)]"
                             aria-hidden="true">
                            <defs>
                                <linearGradient id="plane" x1="0" x2="1">
                                    <stop offset="0" stop-color="#ffffff" stop-opacity="0.92"></stop>
                                    <stop offset="1" stop-color="#e2e8f0" stop-opacity="0.86"></stop>
                                </linearGradient>
                            </defs>
                            <path
                                d="M42 128c-14-6-14-26 0-32L470 10c18-6 34 10 28 28l-86 428c-4 18-26 22-36 6l-98-158-120 64c-10 6-24 2-30-8L42 128z"
                                fill="url(#plane)"/>
                            <path d="M125 138 444 43 218 214z" fill="#cbd5e1" opacity="0.8"/>
                            <path d="M218 214 362 392 444 43z" fill="#e2e8f0" opacity="0.9"/>
                        </svg>

                        <div class="absolute right-[18%] top-[45%] translate-x-1/2 -translate-y-1/2">
                            <div
                                class="h-24 w-24 rounded-[26px] bg-gradient-to-b from-orange-400 to-orange-600 shadow-soft2 grid place-items-center">
                                <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 5v14M5 12h14" stroke="white" stroke-width="3.5"
                                          stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto max-w-xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-2xl bg-white/10 text-white/90 backdrop-blur px-3 py-2 text-xs font-bold mb-4">
                            <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                            <span id="heroTag">ویژه کاربران</span>
                        </div>

                        <div class="mt-7">
                            <a id="heroCta" href="#"
                               class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-8 py-3 text-sm font-extrabold text-white shadow-soft2
                        hover:bg-orange-600 active:bg-orange-700 transition">
                                مشاهده پیشنهاد
                            </a>
                        </div>


                        <div class="mt-8 flex items-center justify-center gap-2" id="heroDots"
                             aria-label="Slider dots"></div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="relative hidden md:block bg-slate-50">
            <div class="w-[620px] max-w-[55vw] h-full flex items-center justify-center px-10">
                <div class="w-full max-w-lg rounded-2xl bg-white shadow-soft2 border border-slate-200 p-8">
                    <livewire:front.identity.user.auth.reset-password/>
                </div>
            </div>
        </aside>

        <section class="md:hidden w-full bg-slate-50 px-4 py-8">
            <div class="mx-auto max-w-md rounded-2xl bg-white shadow-soft2 border border-slate-200 p-6">
                <div id="mobile-auth-mount"></div>
            </div>
        </section>
    </main>
@endsection
