<article
    class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">

    <div class="p-5 pb-0 sm:p-6 sm:pb-0">
        <div class="text-center">
            <div
                class="relative inline-block overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                <x-front.responsive-image
                    :media="$page->picture"
                    :fallback="$page->picture?->url"
                    :alt="$page->title"
                    :title="$page->title"
                    variant="large"
                    class="block max-w-full h-auto"
                    wrapper-class="w-full"
                    :lazy="false"
                    :async="false"
                    :skeleton="false"
                    fetchpriority="high"
                    sizes="(max-width: 768px) 100vw, 1280px"
                />

                <div class="absolute inset-0 bg-gradient-to-t from-black/0 via-black/30 to-transparent"></div>

            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6">
        <div class="tinymce-content dark:prose-invert dark:text-slate-200">
            {!! $page->body !!}
        </div>
    </div>
</article>
