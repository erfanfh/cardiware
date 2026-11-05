@props(['userId' => null])

<a
    href="{{ $userId ? route('loyalty.members.show', $userId) : route('vip.index') }}"
    title="کاربر دارای اشتراک VIP فعال؛ مشاهده کارت باشگاه"
    aria-label="کاربر دارای اشتراک VIP فعال؛ مشاهده کارت باشگاه"
    class="inline-flex h-5 shrink-0 cursor-pointer items-center gap-1 rounded-lg border border-amber-300 bg-amber-50 px-1.5 text-[10px] font-black leading-none text-amber-800 transition hover:bg-amber-100 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20"
>
    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="m3 8 4 4 5-7 5 7 4-4-2 11H5L3 8Z" />
    </svg>
    <span>VIP</span>
</a>
