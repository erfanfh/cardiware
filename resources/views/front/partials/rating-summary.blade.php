@if(isset($averageRating) && $averageRating !== null && ($ratingCount ?? 0) > 0)
    <span
        class="inline-flex h-6 w-fit shrink-0 items-center gap-1 rounded-lg border border-amber-200/80 bg-amber-50 px-2 text-[0.625rem] font-extrabold leading-none text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200"
        aria-label="میانگین امتیاز {{ number_format($averageRating, 2) }} از ۵، بر اساس {{ number_format($ratingCount) }} امتیاز"
        title="میانگین امتیاز کاربران"
    >
        <svg viewBox="0 0 20 20" class="h-3 w-3 text-amber-400" aria-hidden="true">
            <path
                fill="currentColor"
                d="M10 15.27l-5.18 2.73.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
            />
        </svg>

        <span dir="ltr">{{ number_format($averageRating, 2) }} / 5</span>
        <span aria-hidden="true">·</span>
        <span>{{ number_format($ratingCount) }} امتیاز</span>
    </span>
@endif
