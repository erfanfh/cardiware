<div
    class="rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-50 to-white p-5 shadow-sm dark:border-slate-800 dark:from-brand-500/10 dark:to-slate-900 dark:shadow-black/20">
    <div class="flex items-center gap-3">
        <div class="h-11 w-11 rounded-2xl bg-brand-500 text-white grid place-items-center shadow-sm">★</div>
        <div>
            <div class="font-extrabold dark:text-slate-100">{{ get_setting('home', 'subscription_title') }}</div>
            <div
                class="text-xs text-slate-600 dark:text-slate-400">{{ get_setting('home', 'subscription_subtitle') }}</div>
        </div>
    </div>
    <a href="{{ in_array(get_setting('home', 'subscription_button_url'), [null, '', '#'], true) ? route('vip.index') : get_setting('home', 'subscription_button_url') }}"
       class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-brand-500 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition shadow-lg dark:shadow-black/20 dark:hover:bg-brand-400">
        {{ get_setting('home', 'subscription_button_title') }}
    </a>
</div>
