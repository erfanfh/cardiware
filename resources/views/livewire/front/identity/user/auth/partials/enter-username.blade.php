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
            فراموشی رمز عبور
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            شماره موبایل یا ایمیل خود را وارد کنید
        </p>
    </div>

    <form wire:submit.prevent="sendResetPasswordOtp" class="space-y-4" novalidate>
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

        <button type="submit"
                wire:loading.attr="disabled"
                class="relative flex w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5
                       text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300
                       hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99]
                       disabled:opacity-70 cursor-pointer">

            <span wire:loading.remove wire:target="sendResetPasswordOtp">
                دریافت کد تایید
            </span>

            <svg wire:loading wire:target="sendResetPasswordOtp"
                 class="h-5 w-5 animate-spin"
                 viewBox="0 0 24 24"
                 fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        </button>

        <div class="pt-1 text-center text-xs text-slate-500">
            رمز عبور را به خاطر دارید؟
            <button type="button"
                    wire:click.prevent="goToLogin"
                    class="mr-1 font-semibold text-brand-600 hover:text-brand-700 transition-colors cursor-pointer">
                ورود
            </button>
        </div>
    </form>
</div>
