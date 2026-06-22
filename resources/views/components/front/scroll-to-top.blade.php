<button
    id="scrollToTopBtn"
    type="button"
    aria-label="پرش به بالای صفحه"
    class="
        cursor-pointer
        group fixed bottom-5 left-5 z-[9999]
        flex h-[52px] w-[52px] items-center justify-center
        overflow-hidden
        rounded-[22px]

        border border-orange-300/70
        bg-orange-500
        text-white
        shadow-[0_14px_34px_rgba(249,115,22,0.28),0_8px_18px_rgba(15,23,42,0.12)]

        opacity-0 invisible pointer-events-none
        translate-y-4 scale-95
        transition-all duration-300 ease-out

        hover:-translate-y-1
        hover:border-orange-300
        hover:bg-orange-600
        hover:shadow-[0_18px_44px_rgba(249,115,22,0.38),0_10px_24px_rgba(15,23,42,0.18)]

        active:translate-y-0 active:scale-95
        focus-visible:outline-none
        focus-visible:ring-4
        focus-visible:ring-orange-400/25

        dark:border-orange-400/25
        dark:bg-orange-500
        dark:text-white
        dark:shadow-[0_18px_46px_rgba(249,115,22,0.26),0_10px_28px_rgba(0,0,0,0.42)]

        dark:hover:border-orange-300/45
        dark:hover:bg-orange-400
        dark:hover:shadow-[0_22px_54px_rgba(249,115,22,0.36),0_12px_32px_rgba(0,0,0,0.52)]

        md:bottom-6 md:left-6 md:h-14 md:w-14
    "
>
    {{-- لایه داخلی برای خروج از حالت مربع ساده --}}
    <span
        class="
            pointer-events-none absolute inset-[5px]
            rounded-[18px]
            border border-white/20
            bg-white/10
            transition-all duration-300

            group-hover:inset-[4px]
            group-hover:rounded-[19px]
            group-hover:bg-white/14
        "
    ></span>

    {{-- هایلایت گوشه‌ای خیلی ملایم --}}
    <span
        class="
            pointer-events-none absolute -top-5 -left-5
            h-14 w-14 rounded-full
            bg-white/18 blur-xl
            transition-transform duration-500
            group-hover:scale-125
        "
    ></span>

    <svg
        class="
            relative z-10 h-[22px] w-[22px]
            drop-shadow-sm
            transition-transform duration-300
            group-hover:-translate-y-0.5
        "
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true"
    >
        <path
            d="M12 19V5M12 5L5.5 11.5M12 5L18.5 11.5"
            stroke="currentColor"
            stroke-width="2.35"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</button>
