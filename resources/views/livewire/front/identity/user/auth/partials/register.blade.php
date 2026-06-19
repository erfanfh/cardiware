@extends('livewire.front.identity.user.auth.partials.layout')

@section('auth-title', 'ثبت‌نام')
@section('auth-description', 'اطلاعات خود را برای ایجاد حساب وارد کنید')

@section('auth-form')
    <form wire:submit.prevent="register"
          class="space-y-4"
          novalidate
          x-data="{ show1:false, show2:false }">

        <div class="grid gap-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="min-w-0 space-y-1.5">
                    <label for="mobile" class="block text-xs font-semibold text-slate-700">موبایل</label>
                    <input id="mobile" type="text" wire:model.defer="mobile" dir="ltr"
                           class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"/>
                </div>

                <div class="min-w-0 space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-slate-700">ایمیل</label>
                    <input id="email" type="text" wire:model.defer="email" dir="ltr"
                           class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"/>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="min-w-0 space-y-1.5">
                    <label for="first_name" class="block text-xs font-semibold text-slate-700">نام</label>
                    <input id="first_name" type="text" wire:model.defer="first_name"
                           class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"/>
                </div>

                <div class="min-w-0 space-y-1.5">
                    <label for="last_name" class="block text-xs font-semibold text-slate-700">نام خانوادگی</label>
                    <input id="last_name" type="text" wire:model.defer="last_name"
                           class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"/>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="min-w-0 space-y-1.5">
                    <label for="reg-password" class="block text-xs font-semibold text-slate-700">رمز عبور</label>

                    <div class="relative">
                        <input id="reg-password" :type="show1 ? 'text':'password'"
                               wire:model.defer="password" dir="ltr" autocomplete="off"
                               class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 pl-10 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"/>

                        <button type="button" @click="show1 = !show1"
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600 transition-colors cursor-pointer">
                            <svg x-show="!show1" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                 viewBox="0 0 24 24">
                                <g fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path
                                        d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"/>
                                    <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z"/>
                                </g>
                            </svg>
                            <svg x-show="show1" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                 viewBox="0 0 24 24">
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

                <div class="min-w-0 space-y-1.5">
                    <label for="reg-password-confirm" class="block text-xs font-semibold text-slate-700">تکرار رمز
                        عبور</label>

                    <div class="relative">
                        <input id="reg-password-confirm" :type="show2 ? 'text':'password'"
                               wire:model.defer="password_confirmation" dir="ltr" autocomplete="off"
                               class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 pl-10 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"/>

                        <button type="button" @click="show2 = !show2"
                                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-600 transition-colors cursor-pointer">
                            <svg x-show="!show2" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                 viewBox="0 0 24 24">
                                <g fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path
                                        d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"/>
                                    <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z"/>
                                </g>
                            </svg>
                            <svg x-show="show2" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                 viewBox="0 0 24 24">
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
            </div>
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                class="relative flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300 hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99] disabled:opacity-70">
            <span wire:loading.remove wire:target="register">ایجاد حساب کاربری</span>

            <svg wire:loading wire:target="register" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        </button>

        <div class="pt-1 text-center text-xs leading-6 text-slate-500">
            حساب کاربری دارید؟
            <button type="button"
                    wire:click.prevent="goToLogin"
                    class="mr-1 cursor-pointer font-semibold text-brand-600 transition-colors hover:text-brand-700">
                ورود
            </button>
        </div>
    </form>
@endsection
