<div wire:loading.class="opacity-60" class="transition font-sans">
    @if (session()->has('success'))
        <div class="mb-6 rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-4 text-sm text-amber-800 shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-orange-50 px-5 py-4 text-sm text-red-700 shadow-sm">
            <div class="flex items-center gap-2 font-bold mb-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                خطا در ارسال اطلاعات:
            </div>
            <ul class="space-y-2 pr-5">
                @foreach ($errors->all() as $error)
                    <li class="flex items-start gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-400 mt-2 flex-shrink-0"></span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl bg-gradient-to-br from-white to-orange-50/50 shadow-2xl shadow-orange-100/50 ring-1 ring-orange-100 overflow-hidden backdrop-blur-sm">
        <div class="p-6 sm:p-8">

            <!-- لوگوی سفارشی -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-400 to-amber-400 blur-xl opacity-30 rounded-full"></div>
                    <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            @if($step === 'enter-mobile')
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
                        خوش آمدید
                    </h1>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed max-w-xs mx-auto">
                        شماره موبایل خود را وارد کنید تا کد تایید برای شما ارسال شود
                    </p>
                </div>

                <form wire:submit.prevent="submitMobile" class="space-y-6" novalidate>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2" for="mobile">
                            <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            شماره موبایل
                        </label>
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-500"></div>
                            <div class="relative">
                                <input
                                    id="mobile"
                                    type="tel"
                                    inputmode="numeric"
                                    placeholder="مثال: ۰۹۱۲۳۴۵۶۷۸۹"
                                    wire:model.defer="mobile"
                                    class="w-full h-14 pr-14 pl-5 rounded-xl bg-white/80 border border-orange-100 text-[15px] placeholder:text-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                           shadow-sm backdrop-blur-sm"
                                    dir="ltr"
                                />
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-orange-400">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 8v4l3 3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm
                                   shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/35
                                   hover:from-orange-600 hover:to-amber-600 active:scale-[0.98]
                                   transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer">
                        <span>ادامه و دریافت کد</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            @endif

            @if($step === 'login')
                <button wire:click.prevent="resetToMobile"
                        class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    بازگشت به مرحله قبل
                </button>

                <div class="mb-8 text-center">
                    <h2 class="text-xl font-bold text-slate-800">ورود به حساب کاربری</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        رمز عبور خود را وارد کنید
                    </p>
                    <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-full">
                        <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-700">{{ $mobile }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="login" class="space-y-6" novalidate x-data="{ show:false }">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2" for="login-password">
                            <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            رمز عبور
                        </label>
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-500"></div>
                            <div class="relative">
                                <input
                                    id="login-password"
                                    :type="show ? 'text' : 'password'"
                                    wire:model.defer="password"
                                    autocomplete="off"
                                    dir="ltr"
                                    class="w-full h-14 pr-14 pl-12 rounded-xl bg-white/80 border border-orange-100 text-[15px] placeholder:text-slate-400
                                           focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                           shadow-sm backdrop-blur-sm"
                                    placeholder="رمز عبور شما"
                                />
                                <button type="button"
                                        @click="show = !show"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 inline-flex h-10 w-10 items-center justify-center rounded-xl
                                               bg-white border border-orange-200 hover:bg-orange-50 hover:border-orange-300
                                               transition-colors cursor-pointer"
                                        aria-label="نمایش/مخفی رمز">
                                    <svg class="h-5 w-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm
                                   shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/35
                                   hover:from-orange-600 hover:to-amber-600 active:scale-[0.98]
                                   transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>ورود به حساب</span>
                    </button>

                    <div class="text-center pt-4 border-t border-orange-100">
                        <button type="button" wire:click="goToForgotPassword"
                                class="text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors inline-flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            رمز عبور را فراموش کرده‌اید؟
                        </button>
                    </div>
                </form>
            @endif

            @if($step === 'verify-otp')
                <div class="mb-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-100 to-amber-100 mb-4">
                        <svg class="w-8 h-8 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12.08V12a10 10 0 1 0-5.93 9.14"/>
                            <path d="M22 4L12 14.01l-3-3"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">تایید شماره موبایل</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        کد ارسال‌شده به شماره زیر را وارد کنید
                    </p>
                    <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-full">
                        <span class="text-sm font-medium text-slate-700">{{ $mobile }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="verifyOtp" class="space-y-6" novalidate>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2" for="otp_code">
                            <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            کد تایید ۶ رقمی
                        </label>
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-500"></div>
                            <div class="relative">
                                <input
                                    id="otp_code"
                                    type="text"
                                    dir="ltr"
                                    autocomplete="off"
                                    wire:model.defer="otp_code"
                                    placeholder="_ _ _ _ _ _"
                                    class="w-full h-14 px-5 rounded-xl bg-white/80 border border-orange-100 text-center text-xl tracking-widest
                                           focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                           shadow-sm backdrop-blur-sm placeholder:tracking-normal placeholder:text-slate-300"
                                    maxlength="6"
                                />
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm
                                   shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/35
                                   hover:from-orange-600 hover:to-amber-600 active:scale-[0.98]
                                   transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>تایید و ادامه</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-orange-100 text-center"
                     x-data="{ seconds: 60, timer: null, start(){ this.seconds=60; this.timer=setInterval(()=>{ this.seconds--; if(this.seconds<=0){ clearInterval(this.timer); }},1000);} }"
                     x-init="start()"
                >
                    <p class="text-sm text-slate-500 mb-3">کد دریافت نکرده‌اید؟</p>
                    <button
                        :disabled="seconds > 0"
                        @click="$wire.resendOtp(); start();"
                        class="text-sm font-medium transition-all inline-flex items-center gap-2"
                        :class="seconds > 0
                            ? 'text-slate-400 cursor-not-allowed'
                            : 'text-orange-600 hover:text-orange-700 hover:gap-3 cursor-pointer'"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                        </svg>
                        <span x-show="seconds === 0">درخواست کد جدید</span>
                        <span x-show="seconds > 0" class="inline-flex items-center gap-1">
                            <span x-text="seconds"></span> ثانیه تا ارسال مجدد
                        </span>
                    </button>
                </div>
            @endif

            @if($step === 'register')
                <button wire:click.prevent="resetToMobile"
                        class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    بازگشت به مرحله قبل
                </button>

                <div class="mb-8 text-center">
                    <h2 class="text-xl font-bold text-slate-800">تکمیل ثبت‌نام</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        اطلاعات خود را برای ایجاد حساب وارد کنید
                    </p>
                    <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-full">
                        <span class="text-sm font-medium text-slate-700">{{ $mobile }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="register" class="space-y-6" novalidate x-data="{ show1:false, show2:false }">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700" for="first_name">نام</label>
                            <input id="first_name" type="text" wire:model.defer="first_name"
                                   class="w-full h-12 px-4 rounded-xl bg-white/80 border border-orange-100 text-[14px]
                                          focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                          shadow-sm placeholder:text-slate-400"
                                   placeholder="مانند: محمد" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700" for="last_name">نام خانوادگی</label>
                            <input id="last_name" type="text" wire:model.defer="last_name"
                                   class="w-full h-12 px-4 rounded-xl bg-white/80 border border-orange-100 text-[14px]
                                          focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                          shadow-sm placeholder:text-slate-400"
                                   placeholder="مانند: محمدی" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                رمز عبور
                            </label>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="relative">
                                    <input id="reg-password" :type="show1 ? 'text':'password'" wire:model.defer="password" dir="ltr" autocomplete="off"
                                           class="w-full h-12 pr-4 pl-11 rounded-xl bg-white/80 border border-orange-100 text-[14px]
                                                  focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                                  shadow-sm placeholder:text-slate-400"
                                           placeholder="حداقل ۸ کاراکتر" />
                                    <button type="button" @click="show1=!show1"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 inline-flex h-9 w-9 items-center justify-center rounded-xl
                                                   bg-white border border-orange-200 hover:bg-orange-50 hover:border-orange-300
                                                   transition-colors cursor-pointer"
                                            aria-label="نمایش/مخفی رمز">
                                        <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="relative">
                                    <input id="reg-password-confirm" :type="show2 ? 'text':'password'" wire:model.defer="password_confirmation" dir="ltr" autocomplete="off"
                                           class="w-full h-12 pr-4 pl-11 rounded-xl bg-white/80 border border-orange-100 text-[14px]
                                                  focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                                  shadow-sm placeholder:text-slate-400"
                                           placeholder="تکرار رمز عبور" />
                                    <button type="button" @click="show2=!show2"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 inline-flex h-9 w-9 items-center justify-center rounded-xl
                                                   bg-white border border-orange-200 hover:bg-orange-50 hover:border-orange-300
                                                   transition-colors cursor-pointer"
                                            aria-label="نمایش/مخفی رمز">
                                        <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">رمز عبور باید حداقل ۸ کاراکتر و شامل حروف و اعداد باشد</p>
                    </div>

                    <button type="submit"
                            class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm
                                   shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/35
                                   hover:from-orange-600 hover:to-amber-600 active:scale-[0.98]
                                   transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <path d="M20 8v6M23 11h-6"/>
                        </svg>
                        <span>ایجاد حساب کاربری</span>
                    </button>
                </form>
            @endif

            @if($step === 'forgot-password')
                <button wire:click.prevent="resetToMobile"
                        class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    تغییر شماره موبایل
                </button>

                <div class="mb-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-100 to-amber-100 mb-4">
                        <svg class="w-8 h-8 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">بازیابی رمز عبور</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        کد تایید و رمز جدید خود را وارد کنید
                    </p>
                    <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-full">
                        <span class="text-sm font-medium text-slate-700">{{ $mobile }}</span>
                    </div>
                </div>

                <form wire:submit.prevent="forgotPassword" class="space-y-6" novalidate>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700" for="fp-pass">رمز عبور جدید</label>
                            <input id="fp-pass" type="password" dir="ltr" autocomplete="off" wire:model.defer="password"
                                   class="w-full h-12 px-4 rounded-xl bg-white/80 border border-orange-100 text-[14px]
                                          focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                          shadow-sm placeholder:text-slate-400"
                                   placeholder="رمز جدید" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700" for="fp-pass2">تکرار رمز عبور</label>
                            <input id="fp-pass2" type="password" dir="ltr" autocomplete="off" wire:model.defer="password_confirmation"
                                   class="w-full h-12 px-4 rounded-xl bg-white/80 border border-orange-100 text-[14px]
                                          focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                          shadow-sm placeholder:text-slate-400"
                                   placeholder="تکرار رمز جدید" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2" for="fp-otp">
                            <svg class="w-4 h-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            کد تایید ۶ رقمی
                        </label>
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-500"></div>
                            <div class="relative">
                                <input id="fp-otp" type="text" dir="ltr" autocomplete="off" wire:model.defer="otp_code"
                                       class="w-full h-14 px-5 rounded-xl bg-white/80 border border-orange-100 text-center text-xl tracking-widest
                                              focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent
                                              shadow-sm backdrop-blur-sm placeholder:tracking-normal placeholder:text-slate-300"
                                       placeholder="_ _ _ _ _ _"
                                       maxlength="6" />
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full h-14 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm
                                   shadow-lg shadow-orange-500/25 hover:shadow-xl hover:shadow-orange-500/35
                                   hover:from-orange-600 hover:to-amber-600 active:scale-[0.98]
                                   transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>تغییر رمز عبور</span>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-orange-100 text-center"
                     x-data="{ seconds: 60, timer: null, start(){ this.seconds=60; this.timer=setInterval(()=>{ this.seconds--; if(this.seconds<=0){ clearInterval(this.timer); }},1000);} }"
                     x-init="start()"
                >
                    <p class="text-sm text-slate-500 mb-3">کد دریافت نکرده‌اید؟</p>
                    <button
                        :disabled="seconds > 0"
                        @click="$wire.resendOtp(); start();"
                        class="text-sm font-medium transition-all inline-flex items-center gap-2"
                        :class="seconds > 0
                            ? 'text-slate-400 cursor-not-allowed'
                            : 'text-orange-600 hover:text-orange-700 hover:gap-3 cursor-pointer'"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                        </svg>
                        <span x-show="seconds === 0">درخواست کد جدید</span>
                        <span x-show="seconds > 0" class="inline-flex items-center gap-1">
                            <span x-text="seconds"></span> ثانیه تا ارسال مجدد
                        </span>
                    </button>
                </div>
            @endif

        </div>

        <!-- فوتر -->
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-t border-orange-100">
            <p class="text-center text-xs text-slate-500">
                با ورود یا ثبت‌نام، با
                <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">شرایط استفاده</a>
                و
                <a href="#" class="text-orange-600 hover:text-orange-700 font-medium">حریم خصوصی</a>
                موافقت می‌کنید
            </p>
        </div>
    </div>
</div>
