@extends('livewire.front.identity.user.auth.partials.layout')

@section('auth-title', 'فراموشی رمز عبور')
@section('auth-description', 'شماره موبایل یا ایمیل خود را وارد کنید')

@section('auth-form')
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
                class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100"
            />
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                class="relative flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300 hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99] disabled:opacity-70">
            <span wire:loading.remove wire:target="sendResetPasswordOtp">دریافت کد تایید</span>

            <svg wire:loading wire:target="sendResetPasswordOtp" class="h-5 w-5 animate-spin" viewBox="0 0 24 24"
                 fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        </button>

        <div class="pt-1 text-center text-xs leading-6 text-slate-500">
            رمز عبور را به خاطر دارید؟
            <button type="button"
                    wire:click.prevent="goToLogin"
                    class="mr-1 cursor-pointer font-semibold text-brand-600 transition-colors hover:text-brand-700">
                ورود
            </button>
        </div>
    </form>
@endsection
