@extends('livewire.front.identity.user.auth.partials.layout')

@section('auth-title', 'ورود به حساب کاربری')
@section('auth-description', 'اطلاعات حساب خود را وارد کنید')

@section('auth-form')
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
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"/>
                            <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z"/>
                        </g>
                    </svg>

                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                            <path stroke-linejoin="round"
                                  d="M19.439 15.439a19.5 19.5 0 0 0 2.105-2.484c.304-.426.456-.64.456-.955c0-.316-.152-.529-.456-.955C20.178 9.129 16.689 5 12 5c-.908 0-1.77.155-2.582.418m-2.67 1.33c-2.017 1.36-3.506 3.195-4.292 4.297c-.304.426-.456.64-.456.955c0 .316.152.529.456.955C3.822 14.871 7.311 19 12 19c1.99 0 3.765-.744 5.253-1.747"/>
                            <path d="M9.858 10A2.929 2.929 0 1 0 14 14.142"/>
                            <path stroke-linejoin="round" d="m3 3l18 18"/>
                        </g>
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
@endsection
