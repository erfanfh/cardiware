<div class="flex min-w-0 flex-col gap-5">
    @include(
        'livewire.front.identity.user.auth.partials.shared.step-header',
        [
            'title' => 'Sign in with email',
            'description' => 'Enter your account email and password',
            'backMethod' => 'goToEnterMobile',
        ]
    )

    <form wire:submit.prevent="loginWithEmail" class="space-y-4" novalidate>
        <div class="space-y-1.5">
            <label for="auth-login-email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Email
            </label>
            <input
                id="auth-login-email"
                name="email"
                type="email"
                dir="ltr"
                inputmode="email"
                autocomplete="username"
                maxlength="255"
                wire:model.defer="email"
                class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
            />
        </div>

        <div class="space-y-1.5">
            <label for="auth-email-password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Password
            </label>
            <input
                id="auth-email-password"
                name="password"
                type="password"
                dir="ltr"
                autocomplete="current-password"
                wire:model.defer="password"
                class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
            />
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="loginWithEmail"
            class="flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70"
        >
            <span wire:loading.remove wire:target="loginWithEmail">Sign in</span>
            <span wire:loading wire:target="loginWithEmail">Signing in...</span>
        </button>
    </form>
</div>
