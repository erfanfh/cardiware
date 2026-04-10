<div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-50 to-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="h-11 w-11 rounded-2xl bg-brand-500 text-white grid place-items-center shadow-sm">★</div>
        <div>
            <div class="font-extrabold">{{ get_setting('home', 'subscription_title') }}</div>
            <div class="text-xs text-slate-600">{{ get_setting('home', 'subscription_subtitle') }}</div>
        </div>
    </div>
    <a href="{{ get_setting('home', 'subscription_button_url') }}"
       class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-brand-500 py-3 text-sm font-extrabold text-white hover:bg-brand-600 transition shadow-lg">
        {{ get_setting('home', 'subscription_button_title') }}
    </a>
</div>
