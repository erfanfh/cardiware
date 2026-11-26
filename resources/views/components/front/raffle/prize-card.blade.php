@props(['prize', 'featured' => false])

<article {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20']) }}>
    <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 via-orange-50 to-amber-100 dark:from-brand-950 dark:via-slate-900 dark:to-amber-950">
        @if($prize->mediaFile)
            <x-front.responsive-image
                :media="$prize->mediaFile"
                :fallback="$prize->mediaFile?->url"
                :alt="$prize->title"
                :title="$prize->title"
                variant="medium"
                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                wrapper-class="h-full w-full"
                :skeleton="true"
                loading="lazy"
                decoding="async"
                sizes="(max-width: 768px) 100vw, 420px"
            />
        @else
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,.85),transparent_30%),radial-gradient(circle_at_80%_80%,rgba(249,115,22,.18),transparent_35%)] dark:bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,.06),transparent_30%),radial-gradient(circle_at_80%_80%,rgba(249,115,22,.12),transparent_35%)]"></div>
            <div class="absolute inset-0 flex items-center justify-center text-brand-500 dark:text-brand-300 [&_svg]:h-14 [&_svg]:w-14">
                @include('icons.award-03')
            </div>
        @endif

        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-slate-950/65 via-slate-950/15 to-transparent"></div>

        <div class="absolute right-3 top-3">
            <span class="inline-flex items-center rounded-xl border border-white/20 bg-slate-950/35 px-2.5 py-1.5 text-[11px] font-black text-white backdrop-blur-md">
                {{ number_format($prize->quantity) }} Winner
            </span>
        </div>

        @if($prize->estimated_value)
            <div class="absolute bottom-3 right-3 text-xs font-extrabold text-white">
                Approximate value {{ number_format($prize->estimated_value) }} Toman
            </div>
        @endif
    </div>

    <div class="p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase tracking-wider text-brand-500 dark:text-brand-300">Raffle prize</div>
                <h3 class="mt-1.5 text-base font-black leading-7 text-slate-900 dark:text-white">{{ $prize->title }}</h3>
            </div>

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">
                @include('icons.award-03')
            </span>
        </div>

        @if($prize->description)
            <p class="mt-3 line-clamp-3 text-xs leading-6 text-slate-500 dark:text-slate-400">
                {{ $prize->description }}
            </p>
        @endif

        @if($prize->delivery_description)
            <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-[11px] leading-6 text-slate-500 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400">
                {{ $prize->delivery_description }}
            </div>
        @endif
    </div>
</article>
