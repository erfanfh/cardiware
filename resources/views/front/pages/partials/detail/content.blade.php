<article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="p-5 pb-0 sm:p-6 sm:pb-0">
        <div class="text-center">
            <div class="relative inline-block overflow-hidden rounded-2xl border border-slate-200">
                <img
                    src="{{ $page->picture?->url }}"
                    alt="{{ $page->title }}"
                    title="{{ $page->title }}"
                    class="block max-w-full h-auto"
                />

                <div class="absolute inset-0 bg-gradient-to-t from-black/0 via-black/30 to-transparent"></div>

            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6">
        <div class="tinymce-content">
            {!! $page->body !!}
        </div>
    </div>
</article>
