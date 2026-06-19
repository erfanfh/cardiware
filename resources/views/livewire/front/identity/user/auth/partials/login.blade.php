<div>
    <div class="mb-5">
        <a href="/"
           class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="1.8" d="m15 18l-6-6l6-6"/>
            </svg>
            بازگشت به خانه
        </a>
    </div>

    <div class="mb-5 text-center">
        <h2 class="text-xl font-bold tracking-tight text-slate-900">
            ورود به حساب کاربری
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            اطلاعات حساب خود را وارد کنید
        </p>
    </div>

    <form wire:submit.prevent="login" class="space-y-4" novalidate x-data="{ show:false }">
        <div class="space-y-1.5">
            <label for="username" class="block text-xs font-semibold text-slate-700">
                شماره موبایل یا ایمیل
            </label>

            <input
                id="username"
                type="text"
                dir="ltr"
                wire:model.defer="username"
                class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-slate-50/60
                       px-3.5 py-2.5 text-sm outline-none transition-all
                       focus:bg-white focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
            />
        </div>

        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label for="login-password" class="text-xs font-semibold text-slate-700">
                    رمز عبور
                </label>

                <button type="button"
                        wire:click="goToEnterUsername"
                        class="text-xs font-medium text-slate-500 hover:text-brand-600 transition-colors cursor-pointer">
                    فراموشی رمز؟
                </button>
            </div>

            <div class="relative">
                <input
                    id="login-password"
                    :type="show ? 'text' : 'password'"
                    wire:model.defer="password"
                    autocomplete="off"
                    dir="ltr"
                    class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-slate-50/60
                           px-3.5 py-2.5 pl-10 text-sm outline-none transition-all
                           focus:bg-white focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                />

                <button type="button"
                        @click="show = !show"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600 transition-colors cursor-pointer">

                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                         viewBox="0 0 24 24">
                        <path fill="currentColor"
                              d="M12 5c-7 0-11 7-11 7s4 7 11 7s11-7 11-7s-4-7-11-7m0 12a5 5 0 1 1 0-10a5 5 0 0 1 0 10"/>
                    </svg>

                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                         viewBox="0 0 24 24">
                        <path fill="currentColor"
                              d="m2 5.27l1.28-1.27l18 18L20 23.27l-3.1-3.1c-1.44.53-3.01.83-4.9.83c-7 0-11-7-11-7c1.3-2.2 3.13-4.07 5.47-5.24zm9.58 9.58l-2.85-2.85A3 3 0 0 0 12 15a3 3 0 0 0-.42-.15m8.53.46l-2.4-2.4c.83-.74 1.55-1.59 2.29-2.91c0 0-4-7-11-7c-1.4 0-2.68.24-3.85.65L3.89 2.39C6.19 1.5 8.87 1 12 1c7 0 11 7 11 7c-.92 1.57-1.88 2.85-2.89 3.81"/>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                class="relative w-full rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white
                       shadow-lg shadow-brand-500/20 transition-all duration-300
                       hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99]
                       disabled:opacity-70 cursor-pointer flex items-center justify-center">

            <span wire:loading.remove wire:target="login">
                ورود به حساب
            </span>

            <svg wire:loading wire:target="login"
                 class="h-5 w-5 animate-spin"
                 viewBox="0 0 24 24"
                 fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        </button>

        <button type="button"
                wire:click="goToOtpLogin"
                class="w-full rounded-xl border border-slate-200 bg-white py-2.5 text-sm font-medium text-slate-700
                       transition-all hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 cursor-pointer">
            ورود با کد یکبار مصرف
        </button>

        <div class="pt-1 text-center text-xs text-slate-500">
            حساب کاربری ندارید؟
            <button type="button"
                    wire:click.prevent="goToRegister"
                    class="mr-1 font-semibold text-brand-600 hover:text-brand-700 transition-colors cursor-pointer">
                ثبت نام
            </button>
        </div>
    </form>
</div>
