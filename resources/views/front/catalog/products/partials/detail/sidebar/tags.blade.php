@if($product->tags)
    <x-front.cart class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
        <div class="mb-4 text-sm font-extrabold text-slate-900 dark:text-white">برچسب‌ها</div>

        <div class="flex flex-wrap gap-2">
            @foreach($product->tags as $tag)
                <a
                    href="#"
                    class="rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-[11px] font-extrabold text-slate-700 transition hover:border-brand-100 hover:bg-brand-50 hover:text-brand-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-brand-500/10 dark:hover:text-brand-300"
                >
                    {{ $tag->title }}
                </a>
            @endforeach
        </div>
    </x-front.cart>
@endif
