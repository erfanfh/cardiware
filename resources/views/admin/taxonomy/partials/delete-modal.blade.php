@if($deletingId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" wire:click="cancelDelete"></div>

        <div class="relative w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-soft2">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="flex-1">
                    <div class="text-base font-black text-slate-900">
                        حذف {{ $taxonomyLabel }}
                    </div>

                    <div class="mt-2 text-sm leading-6 text-slate-500">
                        آیا از حذف این {{ $taxonomyLabel }} مطمئن هستید؟ این عملیات قابل بازگشت نیست.
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    wire:click="cancelDelete"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                >
                    انصراف
                </button>

                <button
                    type="button"
                    wire:click="deleteTag"
                    wire:loading.attr="disabled"
                    wire:target="deleteTag"
                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg wire:loading wire:target="deleteTag" class="ml-2 h-4 w-4 animate-spin" viewBox="0 0 24 24"
                         fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                stroke-width="4"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                              stroke-linecap="round"></path>
                    </svg>

                    حذف {{ $taxonomyLabel }}
                </button>
            </div>
        </div>
    </div>
@endif
