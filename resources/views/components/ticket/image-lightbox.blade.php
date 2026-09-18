<div
    x-data="{
        open: false,
        url: '',
        title: '',

        showImage(event) {
            this.url = event.detail.url;
            this.title = event.detail.title || 'تصویر پیوست تیکت';
            this.open = true;
        },

        close() {
            this.open = false;
            this.url = '';
        },
    }"
    x-on:ticket-image-open.window="showImage($event)"
    x-on:keydown.escape.window="close()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 p-3 backdrop-blur-md sm:p-6"
    role="dialog"
    aria-modal="true"
    x-bind:aria-label="title"
>
    <button
        type="button"
        x-on:click="close()"
        class="absolute inset-0 cursor-zoom-out"
        aria-label="بستن نمایش تصویر"
    ></button>

    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="relative flex max-h-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl"
        x-on:click.stop
    >
        <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-3 text-white">
            <div class="min-w-0 truncate text-xs font-bold text-white/75" x-text="title"></div>

            <div class="flex shrink-0 items-center gap-2">
                <a
                    x-bind:href="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-9 items-center justify-center rounded-xl bg-white/10 px-3 text-xs font-bold transition hover:bg-white/20"
                >
                    باز کردن در اندازه اصلی
                </a>

                <button
                    type="button"
                    x-on:click="close()"
                    class="grid h-9 w-9 cursor-pointer place-items-center rounded-xl bg-white/10 transition hover:bg-rose-500"
                    aria-label="بستن"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="min-h-0 overflow-auto p-2 sm:p-4">
            <img
                x-bind:src="url"
                x-bind:alt="title"
                class="mx-auto max-h-[calc(100vh-8rem)] max-w-full rounded-2xl object-contain"
            />
        </div>
    </div>
</div>
