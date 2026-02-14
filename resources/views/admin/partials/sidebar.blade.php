<aside
    class="sidebar"
    id="Sidebar">
    <a href="#"
       class="flex items-center gap-[10px] pt-[8px] pb-[4px] px-[10px] mb-[4px] text-white transition hover:opacity-70">
        <div
            class="w-8 h-8 rounded-[12px] bg-[conic-gradient(from_160deg_at_30%_30%,var(--orange-500),var(--brand-500),var(--brand-400),var(--yellow-500))] flex items-center justify-center text-white font-bold text-[18px]">
            م
        </div>
        <div class="flex flex-col gap-[2px]">
            <div class="text-[0.9rem] font-bold">
                مودینگ وی
            </div>
            <div class="text-[11px] opacity-75">پنل مدیریت آموزشی</div>
        </div>
    </a>

    <nav class="mt-2 flex-1 overflow-y-auto px-1">
        <div class="mb-[18px]">
            <p class="text-[11px] uppercase tracking-[0.08em] opacity-55 mb-2 px-2">داشبورد</p>
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-[10px] px-[14px] py-[10px] rounded-[10px] text-[var(--blue-50)] no-underline text-[0.85rem] transition-colors duration-150 ease-in-out hover:bg-white/6 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] text-white hover:bg-[linear-gradient(135deg,color-mix(in_srgb,var(--brand-500)_95%,white_5%),color-mix(in_srgb,var(--brand-600)_95%,white_5%))] hover:text-white' : '' }}">
                <svg style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                     viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 18v4m-3.5 0l.328-.328c.578-.578.867-.867 1.235-1.02c.367-.152.776-.152 1.594-.152h.686c.818 0 1.226 0 1.594.152c.367.152.656.441 1.234 1.02L15.5 22"/>
                        <path
                            d="M2.5 12c0 2.828 0 4.243.879 5.121C4.257 18 5.672 18 8.5 18h7c2.828 0 4.243 0 5.121-.879c.879-.878.879-2.293.879-5.121V8c0-2.828 0-4.243-.879-5.121C19.743 2 18.328 2 15.5 2h-7c-2.828 0-4.243 0-5.121.879C2.5 3.757 2.5 5.172 2.5 8z"/>
                        <path stroke-linecap="round" d="M8 13v-3m4 3V7m4 6v-2"/>
                    </g>
                </svg>
                <span>نمای کلی سامانه</span>
            </a>
        </div>

        <div class="mb-[18px]">
            <p class="text-[11px] uppercase tracking-[0.08em] opacity-55 mb-2 px-2">مدیریت</p>
            <a href="{{ route('admin.posts.index') }}"
               class="flex items-center gap-[10px] px-[14px] py-[10px] rounded-[10px] text-[var(--blue-50)] no-underline text-[0.85rem] transition-colors duration-150 ease-in-out hover:bg-white/6 hover:text-white {{ request()->routeIs('admin.posts.*') ? 'bg-[linear-gradient(135deg,var(--brand-500),var(--brand-600))] text-white hover:bg-[linear-gradient(135deg,color-mix(in_srgb,var(--brand-500)_95%,white_5%),color-mix(in_srgb,var(--brand-600)_95%,white_5%))] hover:text-white' : '' }}">
                <svg style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M3 11c0-3.75 0-5.625.955-6.939A5 5 0 0 1 5.06 2.955C6.375 2 8.251 2 12 2s5.625 0 6.939.955a5 5 0 0 1 1.106 1.106C21 5.375 21 7.251 21 11v2c0 3.75 0 5.625-.955 6.939a5 5 0 0 1-1.106 1.106C17.625 22 15.749 22 12 22s-5.625 0-6.939-.955a5 5 0 0 1-1.106-1.106C3 18.625 3 16.749 3 13zm12-4H7m3 5H7"/>
                </svg>
                <span>نوشته ها</span>
            </a>
        </div>
    </nav>
</aside>
