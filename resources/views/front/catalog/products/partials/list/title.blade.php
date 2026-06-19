<x-front.cart class="relative overflow-hidden">
    <div
        class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
    <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

    <div>
        @if(get_setting('products', 'first_box_label'))
            <div
                class="mb-5 inline-flex items-center gap-2 rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                {{ get_setting('products', 'first_box_label') }}
            </div>
        @endif

        <h1 class="text-xl font-black leading-tight dark:text-slate-100">
            {{ $boxTitle }}
        </h1>

        @if($boxSubtitle)
            <h2 class="mt-2 text-xs leading-6 text-slate-600 dark:text-slate-400">
                {{ $boxSubtitle }}
            </h2>
        @endif
    </div>
</x-front.cart>
