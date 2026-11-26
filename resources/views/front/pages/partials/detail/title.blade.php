<section class="mb-6">
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
        <div
            class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px] dark:bg-brand-500/10"></div>
        <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

        <x-front.breadcrumbs
            nav-class="text-xs text-slate-500 flex flex-wrap gap-2 dark:text-slate-400"
            link-class="hover:text-brand-700 dark:hover:text-brand-400 font-bold transition"
            current-class="text-slate-600 dark:text-slate-300 font-semibold"
            :fallback-items="[
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $page->title, 'url' => null],
            ]"
        />

        <div class="mt-4 flex flex-col gap-4">
            <h1 class="text-xl sm:text-2xl font-black leading-tight text-slate-900 dark:text-white">
                {{ $page->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span>{{ (($page->published_at) ? \Illuminate\Support\Carbon::parse($page->published_at)->format('d F Y') : '') }}</span>
                <span class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
                <span>{{ $page->author->display_name }}</span>
            </div>
        </div>
    </div>
</section>
