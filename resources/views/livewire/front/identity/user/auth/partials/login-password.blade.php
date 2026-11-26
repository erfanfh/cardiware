<div
    class="flex min-w-0 flex-col gap-5"
    x-data="{ showPassword: false }"
>
    @include(
        'livewire.front.identity.user.auth.partials.shared.step-header',
        [
            'title' => 'Sign in',
            'description' => 'Enter your account password',
            'backMethod' => 'goToEnterMobile',
            'mobile' => $resolvedMobile,
        ]
    )

    <form
        wire:submit.prevent="loginWithPassword"
        class="space-y-4"
        novalidate
    >
        <div class="space-y-1.5">
            <div class="flex items-center justify-between gap-3">
                <label
                    for="login-password"
                    class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    Password
                </label>

                <button
                    type="button"
                    wire:click="requestPasswordResetOtp"
                    wire:loading.attr="disabled"
                    wire:target="requestPasswordResetOtp"
                    class="cursor-pointer text-xs font-medium text-slate-500 transition-colors hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:text-slate-400 dark:hover:text-brand-400"
                >
                    <span
                        wire:loading.remove
                        wire:target="requestPasswordResetOtp"
                    >
                        Forgot password?
                    </span>

                    <span
                        wire:loading
                        wire:target="requestPasswordResetOtp"
                    >
                        Sending code...
                    </span>
                </button>
            </div>

            <div class="relative">
                <input
                    id="login-password"
                    :type="showPassword ? 'text' : 'password'"
                    wire:model.defer="password"
                    autocomplete="current-password"
                    dir="ltr"
                    class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 pl-10 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
                />

                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 transition-colors hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-400"
                    aria-label="Show or hide password"
                >
                    <svg
                        x-show="!showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                    >
                        <g
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"
                            />
                            <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z"/>
                        </g>
                    </svg>

                    <svg
                        x-cloak
                        x-show="showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                    >
                        <g
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linejoin="round"
                                d="M19.439 15.439a19.5 19.5 0 0 0 2.105-2.484c.304-.426.456-.64.456-.955c0-.316-.152-.529-.456-.955C20.178 9.129 16.689 5 12 5c-.908 0-1.77.155-2.582.418m-2.67 1.33c-2.017 1.36-3.506 3.195-4.292 4.297c-.304.426-.456.64-.456.955c0 .316.152.529.456.955C3.822 14.871 7.311 19 12 19c1.99 0 3.765-.744 5.253-1.747"
                            />
                            <path d="M9.858 10A2.929 2.929 0 1 0 14 14.142"/>
                            <path
                                stroke-linejoin="round"
                                d="m3 3l18 18"
                            />
                        </g>
                    </svg>
                </button>
            </div>
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="loginWithPassword"
            class="relative flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300 hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
        >
            <span
                wire:loading.remove
                wire:target="loginWithPassword"
            >
                Sign in
            </span>

            <svg
                wire:loading
                wire:target="loginWithPassword"
                class="h-5 w-5 animate-spin"
                viewBox="0 0 24 24"
                fill="none"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                />
            </svg>
        </button>

        <div class="relative py-1">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
            </div>

            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-[0.7rem] text-slate-400 dark:bg-slate-900 dark:text-slate-500">
                    or
                </span>
            </div>
        </div>

        <button
            type="button"
            wire:click="requestLoginOtp"
            wire:loading.attr="disabled"
            wire:target="requestLoginOtp"
            class="relative flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition-all hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-70 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-500/40 dark:hover:bg-slate-800"
        >
            <svg
                wire:loading.remove
                wire:target="requestLoginOtp"
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"
                />
                <path
                    stroke-linecap="round"
                    d="M10 18h4"
                />
            </svg>

            <span
                wire:loading.remove
                wire:target="requestLoginOtp"
            >
                Sign in with a one-time code
            </span>

            <svg
                wire:loading
                wire:target="requestLoginOtp"
                class="h-5 w-5 animate-spin"
                viewBox="0 0 24 24"
                fill="none"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                />
            </svg>
        </button>
    </form>
</div>
