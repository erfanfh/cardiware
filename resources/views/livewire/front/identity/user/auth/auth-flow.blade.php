<div wire:loading.class="opacity-60" class="transition font-sans">
    @if (session()->has('success'))
        <div
            class="mb-6 rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-4 text-sm text-amber-800">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                          clip-rule="evenodd"/>
                </svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-orange-50 px-5 py-4 text-sm text-red-700">
            <div class="flex items-center gap-2 font-bold mb-3">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                          clip-rule="evenodd"/>
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

    @if($step === 'login')
        <button wire:click.prevent="goToRegister"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M13.5 16v-1.938c1.78-1.393 3-3.062 3-6.645C16.5 5.097 16.077 3 13.539 3c0 0-.896-1-3.047-1C7.455 2 5.5 3.827 5.5 7.417c0 3.583 1.22 5.252 3 6.645V16l-3.716 1.118a4.07 4.07 0 0 0-2.76 2.892c-.136.536.327.99.882.99h10.188m5.406 1v-7M15 18.5h7"/>
            </svg>
            ثبت نام
        </button>

        <div class="mb-8 text-center">
            <h2 class="text-xl font-bold text-slate-800">ورود به حساب کاربری</h2>
            <p class="mt-2 text-sm text-slate-600">
                اطلاعات حساب کاربری خود را وارد کنید.
            </p>
        </div>

        <form wire:submit.prevent="login" class="space-y-6" novalidate x-data="{ show:false }">
            <div class="space-y-2">
                <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="username">
                    شماره موبایل یا ایمیل
                </label>
                <div class="relative group">
                    <div class="relative">
                        <input
                            id="username"
                            type="tel"
                            inputmode="numeric"
                            dir="ltr"
                            wire:model.defer="username"
                            class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm"
                           for="login-password">
                        رمز عبور
                    </label>
                    <div class="relative group">
                        <div class="relative">
                            <input
                                id="login-password"
                                :type="show ? 'text' : 'password'"
                                wire:model.defer="password"
                                autocomplete="off"
                                dir="ltr"
                                class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                            />
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-soft
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer">
                    <span>ورود</span>
                </button>

                <div class="text-center pt-4 border-t border-orange-100">
                    <a href="{{ route('auth.reset-password') }}"
                       class="text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors inline-flex items-center gap-2 cursor-pointer">
                        رمز عبور را فراموش کرده‌اید؟
                    </a>
                    <span>/</span>
                    <button type="button" wire:click="goToOtpLogin"
                            class="text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors inline-flex items-center gap-2 cursor-pointer">
                        ورود با کد یکبار مصرف
                    </button>
                </div>
            </div>
        </form>
    @endif

    @if($step === 'register')
        <button wire:click.prevent="goToLogin"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                   stroke-width="1.5">
                    <path
                        d="M8 8c0-.575 0-.822.045-1.075A2.98 2.98 0 0 1 9.833 4.7c.24-.1.523-.165 1.09-.294l2.728-.623c3.39-.774 5.084-1.161 6.217-.27C21 4.405 21 6.126 21 9.568v4.864c0 3.442 0 5.164-1.132 6.055c-1.133.891-2.827.504-6.217-.27l-2.728-.623c-.567-.13-.85-.194-1.09-.294a2.98 2.98 0 0 1-1.788-2.225C8 16.822 8 16.575 8 16"/>
                    <path d="M13 9s3 2.21 3 3s-3 3-3 3m2.5-3H3"/>
                </g>
            </svg>
            ورود
        </button>

        <div class="mb-8 text-center">
            <h2 class="text-xl font-bold text-slate-800">ثبت‌نام</h2>
            <p class="mt-2 text-sm text-slate-600">
                اطلاعات خود را برای ایجاد حساب را وارد کنید
            </p>
        </div>

        <form wire:submit.prevent="register" class="space-y-6" novalidate x-data="{ show1:false, show2:false }">
            <div class="grid gap-5">
                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="mobile">موبایل</label>
                    <input id="mobile" type="text" wire:model.defer="mobile"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                           placeholder=""/>
                </div>

                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="email">ایمیل</label>
                    <input id="email" type="text" wire:model.defer="email"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                           placeholder=""/>
                </div>

                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="first_name">نام</label>
                    <input id="first_name" type="text" wire:model.defer="first_name"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                           placeholder="مانند: محمد"/>
                </div>

                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="last_name">نام خانوادگی</label>
                    <input id="last_name" type="text" wire:model.defer="last_name"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                           placeholder="مانند: محمدی"/>
                </div>

                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm">
                        رمز عبور
                    </label>
                    <input id="reg-password" :type="show1 ? 'text':'password'"
                           wire:model.defer="password" dir="ltr" autocomplete="off"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                           placeholder="حداقل ۸ کاراکتر"/>
                    <p class="text-[11px] text-slate-400">رمز عبور باید حداقل ۸ کاراکتر و شامل حروف و اعداد
                        باشد</p>
                </div>
                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm">
                        تکرار رمز عبور
                    </label>
                    <input id="reg-password-confirm" :type="show2 ? 'text':'password'"
                           wire:model.defer="password_confirmation" dir="ltr" autocomplete="off"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                           placeholder="تکرار رمز عبور"/>
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-soft
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer">
                <span>ایجاد حساب کاربری</span>
            </button>
        </form>
    @endif

    @if($step === 'otp-login-mobile')
        <button wire:click.prevent="goToLogin"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
            </svg>
            بازگشت
        </button>

        <div class="mb-8 text-center">
            <h2 class="text-xl font-bold text-slate-800">
                ورود به حساب کاربری
            </h2>
            <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-xs mx-auto">
                برای ورود به حساب کاربری، شماره موبایل خود را وارد کنید.
            </p>
        </div>

        <form wire:submit.prevent="sendOtp" class="space-y-6" novalidate>
            <div class="space-y-2">
                <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="username">
                    شماره موبایل
                </label>
                <div class="relative group">
                    <div class="relative">
                        <input
                            id="username"
                            type="tel"
                            inputmode="numeric"
                            dir="ltr"
                            placeholder="09123456789"
                            wire:model.defer="username"
                            class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                        />
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-soft
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer">
                <span>ادامه</span>
            </button>

            <div class="text-center pt-4 border-t border-orange-100">
                <button type="button" wire:click="goToLogin"
                        class="text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors inline-flex items-center gap-2 cursor-pointer">

                    ورود با رمز عبور
                </button>
            </div>
        </form>
    @endif

    @if($step === 'otp-login-verify')
        <button wire:click.prevent="goToLogin"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
            </svg>
            بازگشت
        </button>

        <div class="mb-8 text-center">
            <h2 class="text-xl font-bold text-slate-800">تایید شماره موبایل</h2>
            <p class="mt-2 text-sm text-slate-600">
                کد ارسال‌شده به شماره زیر را وارد کنید
            </p>
            <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-full">
                <span class="text-sm font-medium text-slate-700">{{ $username }}</span>
            </div>
        </div>

        <form wire:submit.prevent="verifyOtp" class="space-y-6" novalidate>
            <div class="space-y-2">
                <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="otp_code">
                    کد تایید ۶ رقمی
                </label>
                <div class="relative group">
                    <div class="relative">
                        <input
                            id="otp_code"
                            type="text"
                            dir="ltr"
                            autocomplete="off"
                            wire:model.defer="otp_code"
                            placeholder="_ _ _ _ _ _"
                            class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                            maxlength="6"
                        />
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-soft
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer">
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
                    <path
                        d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                </svg>
                <span x-show="seconds === 0">درخواست کد جدید</span>
                <span x-show="seconds > 0" class="inline-flex items-center gap-1">
                    <span x-text="seconds"></span> ثانیه تا ارسال مجدد
                </span>
            </button>
        </div>
    @endif
</div>
