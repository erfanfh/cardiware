<div class="flex min-w-0 flex-col gap-5">
    @include(
        'livewire.front.identity.user.auth.partials.shared.step-header',
        [
            'title' => 'Sign in or register',
            'description' => 'Enter your mobile number to continue',
        ]
    )

    <form
        wire:submit.prevent="startAuthFlow"
        class="space-y-4"
        novalidate
    >
        <div class="space-y-1.5">
            <label
                for="auth-mobile"
                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
            >
                Mobile number
            </label>

            <div class="relative">
                <input
                    id="auth-mobile"
                    type="tel"
                    dir="ltr"
                    inputmode="numeric"
                    autocomplete="tel"
                    maxlength="11"
                    wire:model.defer="mobile"
                    placeholder="09123456789"
                    class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 pl-10 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
                />

                <svg
                    class="pointer-events-none absolute left-3.5 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-slate-400 dark:text-slate-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
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
            </div>
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="startAuthFlow"
            class="relative flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300 hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
        >
            <span
                wire:loading.remove
                wire:target="startAuthFlow"
            >
                Continue
            </span>

            <svg
                wire:loading
                wire:target="startAuthFlow"
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

        <p class="text-center text-[0.7rem] leading-6 text-slate-400 dark:text-slate-500">
            If you do not have an account, registration will begin.
        </p>

        @if(get_setting('auth', 'announcement_text'))
            <div
                class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-center text-xs leading-6 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                {!! nl2br(htmlspecialchars(e(get_setting('auth', 'announcement_text')))) !!}
            </div>
        @endif
    </form>

    <button
        type="button"
        wire:click="goToEmailLogin"
        wire:loading.attr="disabled"
        class="flex h-11 w-full cursor-pointer items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
    >
        Sign in with email
    </button>
</div>
