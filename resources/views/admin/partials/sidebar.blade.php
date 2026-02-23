<aside
    class="sidebar bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 border-l border-l-gray-50 dark:border-l-gray-800"
    id="Sidebar">
    <a href="#"
       class="flex items-center gap-[10px] pt-[8px] pb-[4px] px-[10px] mb-[4px] text-gray-700 dark:text-white transition hover:opacity-70">
        <div
            class="w-8 h-8 rounded-[12px] bg-[conic-gradient(from_160deg_at_30%_30%,var(--orange-500),var(--brand-500),var(--brand-400),var(--yellow-500))] flex items-center justify-center text-gray-700 dark:text-white font-bold text-[18px]">
            م
        </div>
        <div class="flex flex-col gap-[2px]">
            <div class="text-[0.9rem] font-bold">
                مودینگ وی
            </div>
            <div class="text-[11px] opacity-75">پنل مدیریت آموزشی</div>
        </div>
    </a>

    <nav>
        <ul class="space-y-2">
            <li>
                <a class="@if (request()->routeIs('admin.dashboard')) bg-gray-100 dark:bg-gray-700 @endif flex items-center px-4 py-2 rounded-md transition-colors duration-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200"
                   href="{{ route('admin.dashboard') }}">
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
                    <span class="mx-4 font-medium text-sm">نمای کلی سامانه</span>
                </a>
            </li>

            <li>
                <a class="@if (request()->routeIs('admin.posts.*')) bg-gray-100 dark:bg-gray-700 @endif flex items-center px-4 py-2 rounded-md transition-colors duration-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200"
                   href="{{ route('admin.posts.index') }}">
                    <svg style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                         viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M8 17h8m-8-4h4m1-10.5V3c0 2.828 0 4.243.879 5.121C14.757 9 16.172 9 19 9h.5m.5 1.657V14c0 3.771 0 5.657-1.172 6.828S15.771 22 12 22s-5.657 0-6.828-1.172S4 17.771 4 14V9.456c0-3.245 0-4.868.886-5.967a4 4 0 0 1 .603-.603C6.59 2 8.211 2 11.456 2c.705 0 1.058 0 1.381.114q.1.036.197.082c.31.148.559.397 1.058.896l4.736 4.736c.579.578.867.868 1.02 1.235c.152.368.152.776.152 1.594"/>
                    </svg>
                    <span class="mx-4 font-medium text-sm">پست ها</span>
                </a>
            </li>

{{--            <li class="relative">--}}
{{--                <a class="@if (request()->routeIs('admin.posts.*')) bg-gray-100 dark:bg-gray-700 @endif flex items-center justify-between px-4 py-2 rounded-md transition-colors duration-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200 cursor-pointer"--}}
{{--                   onclick="document.getElementById('posts').classList.toggle('hidden')">--}}
{{--                    <div class="flex items-center">--}}
{{--                        <svg style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" width="32"--}}
{{--                             height="32"--}}
{{--                             viewBox="0 0 24 24">--}}
{{--                            <g fill="none" stroke="currentColor" stroke-width="1.5">--}}
{{--                                <path stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                      d="M12 18v4m-3.5 0l.328-.328c.578-.578.867-.867 1.235-1.02c.367-.152.776-.152 1.594-.152h.686c.818 0 1.226 0 1.594.152c.367.152.656.441 1.234 1.02L15.5 22"/>--}}
{{--                                <path--}}
{{--                                    d="M2.5 12c0 2.828 0 4.243.879 5.121C4.257 18 5.672 18 8.5 18h7c2.828 0 4.243 0 5.121-.879c.879-.878.879-2.293.879-5.121V8c0-2.828 0-4.243-.879-5.121C19.743 2 18.328 2 15.5 2h-7c-2.828 0-4.243 0-5.121.879C2.5 3.757 2.5 5.172 2.5 8z"/>--}}
{{--                                <path stroke-linecap="round" d="M8 13v-3m4 3V7m4 6v-2"/>--}}
{{--                            </g>--}}
{{--                        </svg>--}}
{{--                        <span class="mx-4 font-medium text-sm">انتخاب واحد</span>--}}
{{--                    </div>--}}
{{--                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"--}}
{{--                         class="bi bi-chevron-down" viewBox="0 0 16 16">--}}
{{--                        <path fill-rule="evenodd"--}}
{{--                              d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>--}}
{{--                    </svg>--}}
{{--                </a>--}}
{{--                <ul id="posts" class="@if (true) hidden @endif mt-2 space-y-2 text-sm">--}}
{{--                    <li>--}}
{{--                        <a class="flex items-center px-4 py-2 ms-2 gap-1.5 rounded-md transition-colors duration-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200"--}}
{{--                           href="#">--}}
{{--                            <svg style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" width="32"--}}
{{--                                 height="32"--}}
{{--                                 viewBox="0 0 24 24">--}}
{{--                                <g fill="none" stroke="currentColor" stroke-width="1.5">--}}
{{--                                    <path stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                          d="M12 18v4m-3.5 0l.328-.328c.578-.578.867-.867 1.235-1.02c.367-.152.776-.152 1.594-.152h.686c.818 0 1.226 0 1.594.152c.367.152.656.441 1.234 1.02L15.5 22"/>--}}
{{--                                    <path--}}
{{--                                        d="M2.5 12c0 2.828 0 4.243.879 5.121C4.257 18 5.672 18 8.5 18h7c2.828 0 4.243 0 5.121-.879c.879-.878.879-2.293.879-5.121V8c0-2.828 0-4.243-.879-5.121C19.743 2 18.328 2 15.5 2h-7c-2.828 0-4.243 0-5.121.879C2.5 3.757 2.5 5.172 2.5 8z"/>--}}
{{--                                    <path stroke-linecap="round" d="M8 13v-3m4 3V7m4 6v-2"/>--}}
{{--                                </g>--}}
{{--                            </svg>--}}
{{--                            <span>درخواست واحد</span>--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </li>--}}
        </ul>
    </nav>
</aside>
