<header
    class="h-[var(--topbar-height)] bg-[var(--bg-topbar)] flex items-center justify-between px-5 py-[10px] shadow-[0_1px_0_var(--border-subtle)] sticky top-0 z-20 ps-[12px] pe-[14px] lg:px-5">
    <div class="flex items-center gap-3 min-w-0">
        <button
            class=" inline-flex lg:hidden items-center justify-center p-[6px] rounded-full bg-[#efefef] cursor-pointer border-0 outline-none transition-transform duration-150 hover:bg-[var(--brand-50)]"
            aria-label="باز کردن منو" id="SidebarToggle">
            <svg viewBox="0 0 24 24" width="20" height="20">
                <path d="M4 7h16M4 12h16M4 17h16"
                      stroke="#0f172a" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="flex flex-col">
            <h1>
                @yield('title')
            </h1>
        </div>
    </div>

    <div class="flex items-center gap-3 relative">
        <div
            class="flex items-center gap-2 ps-3 pe-[5px] py-[5px] rounded-full bg-[#f1f1f1] transition-transform duration-150 hover:shadow-[0_10px_25px_rgba(15,23,42,0.08)] hover:-translate-y-[1px] cursor-pointer"
            id="userMenuButton">
            <div
                class="w-7 h-7 rounded-full bg-[linear-gradient(135deg,var(--brand-500),var(--orange-500))] flex items-center justify-center text-white text-sm font-semibold">
                {{ mb_substr('کاربر', 0, 1) }}
            </div>
            <div class="flex flex-col">
                <div class="text-xs font-semibold">
                    کاربر
                </div>
            </div>
        </div>

        <ul id="userMenu"
            class="hidden absolute top-full left-0 mt-2 w-48 bg-white/90 backdrop-blur-sm border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden transition-all duration-200 origin-top scale-y-0">
            <li>
                <a href="#"
                   class="block px-4 py-2 text-gray-800 hover:bg-gray-100 transition-colors">
                    حساب کاربری
                </a>
            </li>
            <li>
                <hr class="my-1 border-gray-200">
            </li>
            <li>
                <a href="#"
                   class="flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-500 hover:text-white transition-colors font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke-width="1.5"
                                  d="M18 20a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2M4 6.848v10.304c0 1.593 0 2.39.465 2.946c.464.555 1.25.698 2.82.983l3 .544c2.185.397 3.278.595 3.996-.003c.719-.599.719-1.708.719-3.925V6.303c0-2.217 0-3.326-.719-3.925c-.718-.598-1.81-.4-3.997-.003l-3 .544c-1.57.285-2.355.428-2.82.983C4 4.458 4 5.255 4 6.848"/>
                            <path stroke-width="2" d="M11.5 11.998v-.01"/>
                        </g>
                    </svg>
                    خروج
                </a>
            </li>
        </ul>
    </div>

</header>

@push('scripts')
    <script>
        const button = document.getElementById('userMenuButton');
        const menu = document.getElementById('userMenu');

        button.addEventListener('click', () => {
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                setTimeout(() => menu.classList.remove('scale-y-0'), 10);
            } else {
                menu.classList.add('scale-y-0');
                setTimeout(() => menu.classList.add('hidden'), 150);
            }
        });

        document.addEventListener('click', (e) => {
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('scale-y-0');
                setTimeout(() => menu.classList.add('hidden'), 150);
            }
        });
    </script>
@endpush
