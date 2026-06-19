<div class="flex min-w-0 flex-col gap-5">
    <div class="flex justify-end">
        <button wire:click.prevent="goToLogin"
                class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-brand-600">
            @include('icons.arrow-left-01')
            <span>بازگشت</span>
        </button>
    </div>

    <div class="text-center">
        <h2 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
            تایید اطلاعات
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            کد ارسال‌شده را وارد کنید
        </p>

        <div class="mt-3 inline-flex max-w-full items-center rounded-full bg-brand-50 px-3.5 py-1.5">
            <span class="truncate text-xs font-semibold text-slate-700">
                {{ $username }}
            </span>
        </div>
    </div>

    <form wire:submit.prevent="verifyOtp" class="space-y-4" novalidate>
        <div class="space-y-1.5">
            <label for="otp_code" class="block text-xs font-semibold text-slate-700">
                کد تایید ۶ رقمی
            </label>

            <input
                id="otp_code"
                type="text"
                dir="ltr"
                autocomplete="off"
                wire:model.defer="otp_code"
                placeholder="_ _ _ _ _ _"
                maxlength="6"
                class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-center text-sm tracking-[0.35em] outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 sm:tracking-[0.45em]"
            />
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                class="relative flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300 hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99] disabled:opacity-70">
            <span wire:loading.remove wire:target="verifyOtp">تایید و ادامه</span>

            <svg wire:loading wire:target="verifyOtp" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        </button>
    </form>

    <div class="text-center"
         x-data="{ seconds: 60, timer: null, start(){ clearInterval(this.timer); this.seconds=60; this.timer=setInterval(()=>{ this.seconds--; if(this.seconds<=0){ clearInterval(this.timer); }},1000);} }"
         x-init="start()">
        <p class="mb-2 text-xs text-slate-500">
            کد دریافت نکرده‌اید؟
        </p>

        <button
            type="button"
            :disabled="seconds > 0"
            @click="$wire.resendOtp(); start();"
            class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold transition-all"
            :class="seconds > 0
                ? 'text-slate-400 cursor-not-allowed'
                : 'text-brand-600 hover:text-brand-700 cursor-pointer'">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
            </svg>

            <span x-show="seconds === 0">ارسال مجدد کد</span>

            <span x-show="seconds > 0" class="inline-flex items-center gap-1">
                <span x-text="seconds"></span>
                ثانیه تا ارسال مجدد
            </span>
        </button>
    </div>
</div>
