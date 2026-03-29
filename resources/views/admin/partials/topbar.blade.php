<header
    class="h-16 bg-white dark:bg-gray-900 flex items-center justify-between px-5 py-[10px] shadow-[0_1px_0_theme(colors.gray.200)] dark:shadow-[0_1px_0_rgb(75_85_99/0.3)] sticky top-0 z-20 ps-[12px] pe-[14px] lg:px-5">
    <div class="flex items-center gap-3 min-w-0">
        <button
            class="inline-flex lg:hidden items-center justify-center p-[6px] rounded-full bg-[#efefef] dark:bg-gray-800 cursor-pointer border-0 outline-none transition-transform duration-150 hover:bg-brand-50 dark:hover:bg-brand-600"
            aria-label="باز کردن منو" id="SidebarToggle">
            <svg viewBox="0 0 24 24" width="20" height="20">
                <path d="M4 7h16M4 12h16M4 17h16"
                      stroke="#0f172a" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="flex flex-col">
            <h1 class="text-[1rem] font-bold text-black dark:text-white">
                @yield('title')
            </h1>
        </div>
    </div>

    <div class="flex items-center gap-3 relative">
        <button id="theme-toggle"
                class="relative inline-flex h-10 w-20 items-center rounded-full bg-gray-300 dark:bg-gray-700 transition-colors duration-300 focus:outline-none cursor-pointer">

            <span id="toggle-circle"
                  class="absolute left-1 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-md transform transition-transform duration-300 dark:translate-x-10">
                <svg class="h-5 w-5 dark:hidden" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                     viewBox="0 0 24 24">
                    <path fill="none" stroke="#031e3c" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21.5 14.078A8.557 8.557 0 0 1 9.922 2.5C5.668 3.497 2.5 7.315 2.5 11.873a9.627 9.627 0 0 0 9.627 9.627c4.558 0 8.376-3.168 9.373-7.422"/>
                </svg>

                <svg class="hidden h-5 w-5 dark:block" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                     viewBox="0 0 24 24">
                    <g fill="none" stroke="#031e3c" stroke-width="1.5">
                        <path d="M17 12a5 5 0 1 1-10 0a5 5 0 0 1 10 0Z"/>
                        <path stroke-linecap="round"
                              d="M12 2c-.377.333-.905 1.2 0 2m0 16c.377.333.906 1.2 0 2m7.5-17.497c-.532-.033-1.575.22-1.496 1.495M5.496 17.5c.033.532-.22 1.575-1.496 1.496M5.003 4.5c-.033.532.22 1.576 1.497 1.497M18 17.503c.532-.032 1.575.208 1.496 1.414M22 12c-.333-.377-1.2-.905-2 0m-16-.5c-.333.377-1.2.906-2 0"/>
                    </g>
                </svg>
            </span>
        </button>


        <div
            class="flex items-center gap-2 ps-[5px] pe-[12px] py-[5px] rounded-full bg-[#f1f1f1] dark:bg-gray-800 transition-transform duration-150 hover:shadow-[0_10px_25px_rgba(15,23,42,0.08)] dark:hover:shadow-[0_10px_25px_rgba(0,0,0,0.3)] hover:-translate-y-[1px] cursor-pointer"
            id="userMenuButton">
            <div
                class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-orange-500 flex items-center justify-center text-white text-sm font-semibold">
                {{ mb_substr('کاربر', 0, 1) }}
            </div>
            <div class="flex flex-col">
                <div class="text-xs font-semibold dark:text-white">
                    کاربر
                </div>
            </div>
        </div>

        <ul id="userMenu"
            class="hidden absolute top-full left-0 mt-2 w-48 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50 overflow-hidden transition-all duration-200 origin-top scale-y-0">
            <li>
                <a href="#"
                   class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    حساب کاربری
                </a>
            </li>
            <li>
                <hr class="my-1 border-gray-200 dark:border-gray-700">
            </li>
            <li>
                <a href="#"
                   class="flex items-center gap-2 px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-500 dark:hover:bg-red-600 hover:text-white transition-colors font-medium">
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

    <script>
        const toggleButton = document.getElementById('theme-toggle');

        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        toggleButton.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');

            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    </script>

@endpush
