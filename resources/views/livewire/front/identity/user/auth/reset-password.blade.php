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

    @if($step === 'enter-mobile')
        <a href="{{ route('auth.index') }}"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
            </svg>
            بازگشت
        </a>

        <div class="mb-8 text-center">
            <h2 class="text-xl font-bold text-slate-800">
                فراموشی رمز عبور
            </h2>
            <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-xs mx-auto">
                برای ورود به حساب کاربری، شماره موبایل خود را وارد کنید.
            </p>
        </div>

        <form wire:submit.prevent="sendOtp" class="space-y-6" novalidate>
            <div class="space-y-2">
                <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="mobile">
                    شماره موبایل
                </label>
                <div class="relative group">
                    <div class="relative">
                        <input
                            id="mobile"
                            type="tel"
                            inputmode="numeric"
                            dir="ltr"
                            placeholder="09123456789"
                            wire:model.defer="mobile"
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

        </form>
    @endif

    @if($step === 'forgot-password')
        <button wire:click.prevent="goToEnterMobile"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
            </svg>
            تغییر شماره موبایل
        </button>

        <div class="mb-8 text-center">
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
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="fp-pass">رمز عبور جدید</label>
                    <input id="fp-pass" type="password" dir="ltr" autocomplete="off" wire:model.defer="password"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                               outline-none transition focus:border-slate-400"
                           placeholder="رمز جدید"/>
                </div>
                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="fp-pass2">تکرار رمز عبور</label>
                    <input id="fp-pass2" type="password" dir="ltr" autocomplete="off"
                           wire:model.defer="password_confirmation"
                           class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                               outline-none transition focus:border-slate-400"
                           placeholder="تکرار رمز جدید"/>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="fp-otp">
                    کد تایید ۶ رقمی
                </label>
                <div class="relative group">
                    <div class="relative">
                        <input id="fp-otp" type="text" dir="ltr" autocomplete="off" wire:model.defer="otp_code"
                               class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                               outline-none transition focus:border-slate-400 text-center"
                               placeholder="_ _ _ _ _ _"
                               maxlength="6"/>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-soft
                       hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer">
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
