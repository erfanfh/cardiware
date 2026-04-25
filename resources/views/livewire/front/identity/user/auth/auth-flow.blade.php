<div>
    <div
        wire:loading.class="opacity-60"
        class="transition font-sans dark:text-slate-100
            [&_h2]:dark:text-slate-100
            [&_p]:dark:text-slate-400
            [&_label]:dark:text-slate-300
            [&_input]:dark:border-slate-700
            [&_input]:dark:bg-slate-950
            [&_input]:dark:text-slate-100
            [&_input]:dark:placeholder:text-slate-500
            [&_input]:dark:focus:border-orange-400
            [&_button.text-slate-600]:dark:text-slate-300
            [&_a.text-slate-600]:dark:text-slate-300
            [&_.border-orange-100]:dark:border-orange-500/20
            [&_.bg-orange-50]:dark:bg-orange-500/10
            [&_.text-slate-800]:dark:text-slate-100
            [&_.text-slate-700]:dark:text-slate-200
            [&_.text-slate-600]:dark:text-slate-300
            [&_.text-slate-500]:dark:text-slate-400
            [&_.text-slate-400]:dark:text-slate-500"
    >
        @if (session()->has('success'))
            <div
                class="mb-6 rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-orange-500/20 dark:from-orange-500/10 dark:to-amber-500/10 dark:text-amber-300">
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
                class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-orange-50 px-5 py-4 text-sm text-red-700 dark:border-red-500/20 dark:from-red-500/10 dark:to-orange-500/10 dark:text-red-300">
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
            <div class="flex justify-between">
                <button wire:click.prevent="goToRegister"
                        class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M13.5 16v-1.938c1.78-1.393 3-3.062 3-6.645C16.5 5.097 16.077 3 13.539 3c0 0-.896-1-3.047-1C7.455 2 5.5 3.827 5.5 7.417c0 3.583 1.22 5.252 3 6.645V16l-3.716 1.118a4.07 4.07 0 0 0-2.76 2.892c-.136.536.327.99.882.99h10.188m5.406 1v-7M15 18.5h7"/>
                    </svg>
                    ثبت نام
                </button>

                <a href="/"
                   class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                           stroke-width="1.5">
                            <path
                                d="M3 11.99v2.51c0 3.3 0 4.95 1.025 5.975S6.7 21.5 10 21.5h4c3.3 0 4.95 0 5.975-1.025S21 17.8 21 14.5v-2.51c0-1.682 0-2.522-.356-3.25s-1.02-1.244-2.346-2.276l-2-1.555C14.233 3.303 13.2 2.5 12 2.5s-2.233.803-4.298 2.409l-2 1.555C4.375 7.496 3.712 8.012 3.356 8.74S3 10.308 3 11.99"/>
                            <path d="M15 17c-.8.622-1.85 1-3 1s-2.2-.378-3-1"/>
                        </g>
                    </svg>
                    بازگشت به خانه
                </a>
            </div>

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
                                type="text"
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
                            wire:loading.attr="disabled"
                            class="relative w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-lg
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer
                           flex items-center justify-center">

                    <span wire:loading.remove wire:target="login">
                        ورود
                    </span>

                        <svg wire:loading wire:target="login" aria-hidden="true"
                             class="w-5 h-5 text-neutral-tertiary animate-spin fill-brand" viewBox="0 0 100 101"
                             fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                fill="currentColor"/>
                            <path
                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                fill="currentFill"/>
                        </svg>
                    </button>

                    <div class="text-center pt-4 border-t border-orange-100">
                        <button type="button" wire:click="goToEnterUsername"
                                class="text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors inline-flex items-center gap-2 cursor-pointer">
                            رمز عبور را فراموش کرده‌اید؟
                        </button>
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
            <div class="flex justify-between">
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

                <a href="/"
                   class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                           stroke-width="1.5">
                            <path
                                d="M3 11.99v2.51c0 3.3 0 4.95 1.025 5.975S6.7 21.5 10 21.5h4c3.3 0 4.95 0 5.975-1.025S21 17.8 21 14.5v-2.51c0-1.682 0-2.522-.356-3.25s-1.02-1.244-2.346-2.276l-2-1.555C14.233 3.303 13.2 2.5 12 2.5s-2.233.803-4.298 2.409l-2 1.555C4.375 7.496 3.712 8.012 3.356 8.74S3 10.308 3 11.99"/>
                            <path d="M15 17c-.8.622-1.85 1-3 1s-2.2-.378-3-1"/>
                        </g>
                    </svg>
                    بازگشت به خانه
                </a>
            </div>

            <div class="mb-8 text-center">
                <h2 class="text-xl font-bold text-slate-800">ثبت‌نام</h2>
                <p class="mt-2 text-sm text-slate-600">
                    اطلاعات خود را برای ایجاد حساب را وارد کنید
                </p>
            </div>

            <form wire:submit.prevent="register" class="space-y-6" novalidate x-data="{ show1:false, show2:false }">
                <div class="grid gap-5">

                    <!-- موبایل -->
                    <div class="space-y-2">
                        <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm"
                               for="mobile">موبایل</label>
                        <input id="mobile" type="text" wire:model.defer="mobile"
                               class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                      outline-none transition focus:border-slate-400"/>
                    </div>

                    <!-- ایمیل -->
                    <div class="space-y-2">
                        <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm"
                               for="email">ایمیل</label>
                        <input id="email" type="text" wire:model.defer="email"
                               class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                      outline-none transition focus:border-slate-400"/>
                    </div>

                    <!-- نام و نام خانوادگی -->
                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm"
                                   for="first_name">نام</label>
                            <input id="first_name" type="text" wire:model.defer="first_name"
                                   class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                          outline-none transition focus:border-slate-400"
                                   placeholder="مانند: محمد"/>
                        </div>

                        <div class="space-y-2">
                            <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm"
                                   for="last_name">نام خانوادگی</label>
                            <input id="last_name" type="text" wire:model.defer="last_name"
                                   class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                          outline-none transition focus:border-slate-400"
                                   placeholder="مانند: محمدی"/>
                        </div>
                    </div>

                    <!-- رمز و تکرار رمز -->
                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm">
                                رمز عبور
                            </label>
                            <input id="reg-password" :type="show1 ? 'text':'password'"
                                   wire:model.defer="password" dir="ltr" autocomplete="off"
                                   class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                          outline-none transition focus:border-slate-400"
                                   placeholder="حداقل ۸ کاراکتر"/>
                            <p class="text-[11px] text-slate-400">
                                رمز عبور باید حداقل ۸ کاراکتر و شامل حروف و اعداد باشد
                            </p>
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

                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="relative w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-lg
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer
                           flex items-center justify-center">

                    <span wire:loading.remove wire:target="register">
                        ایجاد حساب کاربری
                    </span>

                    <svg wire:loading wire:target="register" aria-hidden="true"
                         class="w-5 h-5 text-neutral-tertiary animate-spin fill-brand" viewBox="0 0 100 101" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"/>
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"/>
                    </svg>
                </button>
            </form>
        @endif

        @if($step === 'otp-login-username')
            <div class="flex justify-between">
                <button wire:click.prevent="goToLogin"
                        class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
                    </svg>
                    بازگشت
                </button>

                <a href="/"
                   class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                           stroke-width="1.5">
                            <path
                                d="M3 11.99v2.51c0 3.3 0 4.95 1.025 5.975S6.7 21.5 10 21.5h4c3.3 0 4.95 0 5.975-1.025S21 17.8 21 14.5v-2.51c0-1.682 0-2.522-.356-3.25s-1.02-1.244-2.346-2.276l-2-1.555C14.233 3.303 13.2 2.5 12 2.5s-2.233.803-4.298 2.409l-2 1.555C4.375 7.496 3.712 8.012 3.356 8.74S3 10.308 3 11.99"/>
                            <path d="M15 17c-.8.622-1.85 1-3 1s-2.2-.378-3-1"/>
                        </g>
                    </svg>
                    بازگشت به خانه
                </a>
            </div>

            <div class="mb-8 text-center">
                <h2 class="text-xl font-bold text-slate-800">
                    دریافت کد تایید
                </h2>
                <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-xs mx-auto">
                    برای دریافت کد تایید، شماره موبایل یا ایمیل خود را وارد کنید.
                </p>
            </div>

            <form wire:submit.prevent="sendOtp" class="space-y-6" novalidate>
                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="username">
                        شماره موبایل یا ایمیل
                    </label>
                    <div class="relative group">
                        <div class="relative">
                            <input
                                id="username"
                                type="text"
                                dir="ltr"
                                wire:model.defer="username"
                                class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                                   outline-none transition focus:border-slate-400"
                            />
                        </div>
                    </div>
                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="relative w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-lg
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer
                           flex items-center justify-center">

                    <span wire:loading.remove wire:target="sendOtp">
                        ادامه
                    </span>

                    <svg wire:loading wire:target="sendOtp" aria-hidden="true"
                         class="w-5 h-5 text-neutral-tertiary animate-spin fill-brand" viewBox="0 0 100 101" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"/>
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"/>
                    </svg>
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
                <h2 class="text-xl font-bold text-slate-800">تایید اطلاعات</h2>
                <p class="mt-2 text-sm text-slate-600">
                    کد ارسال‌شده به شماره موبایل/ایمیل زیر را وارد کنید
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
                        wire:loading.attr="disabled"
                        class="relative w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-lg
                           hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer
                           flex items-center justify-center">

                    <span wire:loading.remove wire:target="verifyOtp">
                        ادامه
                    </span>

                    <svg wire:loading wire:target="verifyOtp" aria-hidden="true"
                         class="w-5 h-5 text-neutral-tertiary animate-spin fill-brand" viewBox="0 0 100 101" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"/>
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"/>
                    </svg>
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

        @if($step === 'enter-username')
            <div class="flex justify-between">
                <button type="button" wire:click="goToLogin"
                        class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
                    </svg>
                    بازگشت
                </button>

                <a href="/"
                   class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                           stroke-width="1.5">
                            <path
                                d="M3 11.99v2.51c0 3.3 0 4.95 1.025 5.975S6.7 21.5 10 21.5h4c3.3 0 4.95 0 5.975-1.025S21 17.8 21 14.5v-2.51c0-1.682 0-2.522-.356-3.25s-1.02-1.244-2.346-2.276l-2-1.555C14.233 3.303 13.2 2.5 12 2.5s-2.233.803-4.298 2.409l-2 1.555C4.375 7.496 3.712 8.012 3.356 8.74S3 10.308 3 11.99"/>
                            <path d="M15 17c-.8.622-1.85 1-3 1s-2.2-.378-3-1"/>
                        </g>
                    </svg>
                    بازگشت به خانه
                </a>
            </div>

            <div class="mb-8 text-center">
                <h2 class="text-xl font-bold text-slate-800">
                    فراموشی رمز عبور
                </h2>
                <p class="mt-4 text-sm text-slate-600 leading-relaxed mx-auto">
                    برای تغییر رمزعبور، شماره موبایل یا ایمیل خود را وارد کنید.
                </p>
            </div>

            <form wire:submit.prevent="sendResetPasswordOtp" class="space-y-6" novalidate>
                <div class="space-y-2">
                    <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="username">
                        شماره موبایل یا ایمیل
                    </label>
                    <div class="relative group">
                        <div class="relative">
                            <input
                                id="username"
                                type="text"
                                dir="ltr"
                                wire:model.defer="username"
                                class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                           outline-none transition focus:border-slate-400"
                            />
                        </div>
                    </div>
                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="relative w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-lg
                       hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer
                       flex items-center justify-center">

                <span wire:loading.remove wire:target="sendResetPasswordOtp">
                    ادامه
                </span>

                    <svg wire:loading wire:target="sendResetPasswordOtp" aria-hidden="true"
                         class="w-5 h-5 text-neutral-tertiary animate-spin fill-brand" viewBox="0 0 100 101" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"/>
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"/>
                    </svg>
                </button>

            </form>
        @endif

        @if($step === 'forgot-password')
            <button wire:click.prevent="goToEnterUsername"
                    class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-orange-600 transition-colors cursor-pointer group">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="1.5" d="M9 6s6 4.419 6 6s-6 6-6 6"/>
                </svg>
                تغییر شماره موبایل یا ایمیل
            </button>

            <div class="mb-8 text-center">
                <h2 class="text-xl font-bold text-slate-800">بازیابی رمز عبور</h2>
                <p class="mt-2 text-sm text-slate-600">
                    کد تایید و رمز جدید خود را وارد کنید
                </p>
                <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-50 rounded-full">
                    <span class="text-sm font-medium text-slate-700">{{ $username }}</span>
                </div>
            </div>

            <form wire:submit.prevent="forgotPassword" class="space-y-6" novalidate>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="fp-pass">رمز
                            عبور جدید</label>
                        <input id="fp-pass" type="password" dir="ltr" autocomplete="off" wire:model.defer="password"
                               class="no-autofill-bg w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm
                           outline-none transition focus:border-slate-400"
                               placeholder="رمز جدید"/>
                    </div>
                    <div class="space-y-2">
                        <label class="flex font-semibold gap-2 items-center text-slate-700 text-sm" for="fp-pass2">تکرار
                            رمز عبور</label>
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
                        wire:loading.attr="disabled"
                        class="relative w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm text-white shadow-lg
                       hover:bg-orange-600 active:bg-orange-700 transition cursor-pointer
                       flex items-center justify-center">

                <span wire:loading.remove wire:target="forgotPassword">
                    تغییر رمز عبور
                </span>

                    <svg wire:loading wire:target="forgotPassword" aria-hidden="true"
                         class="w-5 h-5 text-neutral-tertiary animate-spin fill-brand" viewBox="0 0 100 101" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                            fill="currentColor"/>
                        <path
                            d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                            fill="currentFill"/>
                    </svg>
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
</div>
