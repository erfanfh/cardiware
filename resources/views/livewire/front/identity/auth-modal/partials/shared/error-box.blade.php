@if($errors->any())
    <div
        data-auth-modal-errors
        tabindex="-1"
        role="alert"
        aria-live="assertive"
        class="mb-4 scroll-mt-24 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 outline-none dark:border-red-900/40 dark:bg-red-900/20"
    >
        <div class="flex items-start gap-3">
            <span
                class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-red-600 ring-1 ring-red-100 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/40"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <path
                        d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </span>

            <div class="min-w-0 flex-1">
                <div class="text-xs font-black text-red-900 dark:text-red-300">
                    Operation failed
                </div>

                <ul class="mt-1.5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="flex items-start gap-1.5 text-[11px] font-bold leading-5 text-red-700 dark:text-red-400">
                            <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-red-500"></span>

                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
