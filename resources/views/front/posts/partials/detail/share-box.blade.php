@php $url = url()->current(); @endphp

<x-front.share-modal name="post-share-{{ $post->id }}" max-width="max-w-xl">
    <div
        x-data="{
            copied: false,

            async copyLink() {
                try {
                    await navigator.clipboard.writeText('{{ $url }}');
                    this.copied = true;

                    setTimeout(() => {
                        this.copied = false;
                    }, 1800);
                } catch (error) {
                    const input = this.$refs.shareInput;
                    input.select();
                    document.execCommand('copy');

                    this.copied = true;

                    setTimeout(() => {
                        this.copied = false;
                    }, 1800);
                }
            },

            async nativeShare() {
                if (navigator.share) {
                    await navigator.share({
                        title: '{{ $post->title }}',
                        text: '{{ $post->excerpt() ?: $post->title }}',
                        url: '{{ $url }}'
                    });
                }
            }
        }"
        class="p-4 sm:p-6 dark:text-slate-100"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-50 px-3 py-1.5 text-xs font-extrabold text-brand-600 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                    اشتراک گذاری
                </div>

                <h3 class="mt-4 text-base font-black leading-8 text-slate-900 sm:text-xl dark:text-slate-100">
                    این صفحه را با دیگران به اشتراک بگذارید
                </h3>

                <p class="mt-2 text-xs leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                    لینک را کپی کنید یا از طریق شبکه های اجتماعی ارسال کنید.
                </p>
            </div>

            <button
                type="button"
                x-on:click="$dispatch('close-modal')"
                class="grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-2xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white [&_svg]:h-5 [&_svg]:w-5"
                aria-label="بستن"
            >
                @include('icons.cancel-01')
            </button>
        </div>

        <div
            class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800/70">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input
                    x-ref="shareInput"
                    type="text"
                    readonly
                    value="{{ $url }}"
                    class="direction-ltr h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 text-left text-sm text-slate-600 outline-none focus:ring-2 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:focus:ring-brand-500/20"
                >

                <button
                    type="button"
                    x-on:click="copyLink()"
                    class="h-11 w-full shrink-0 cursor-pointer rounded-2xl bg-slate-900 px-4 text-sm font-extrabold text-white transition hover:bg-slate-800 sm:w-auto dark:bg-slate-100 dark:text-slate-950 dark:hover:bg-white"
                >
                    <span x-show="!copied">کپی</span>
                    <span x-show="copied" x-cloak>کپی شد</span>
                </button>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
            <a href="https://t.me/share/url?url={{ urlencode($url) }}&text={{ urlencode($post->title) }}"
               target="_blank" rel="nofollow noopener"
               class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-sky-200 hover:bg-sky-50 sm:rounded-3xl sm:p-4 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-sky-500/30 dark:hover:bg-sky-500/10">
                <span
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-sky-50 text-sky-500 transition group-hover:bg-white sm:h-11 sm:w-11 dark:bg-sky-500/10 dark:text-sky-300 dark:group-hover:bg-sky-500/15 [&_svg]:h-5 [&_svg]:w-5 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
                    @include('icons.telegram')
                </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700 dark:text-slate-300">تلگرام</span>
            </a>

            <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $url) }}" target="_blank"
               rel="nofollow noopener"
               class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-emerald-200 hover:bg-emerald-50 sm:rounded-3xl sm:p-4 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-500/10">
                <span
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-50 text-emerald-500 transition group-hover:bg-white sm:h-11 sm:w-11 dark:bg-emerald-500/10 dark:text-emerald-300 dark:group-hover:bg-emerald-500/15 [&_svg]:h-5 [&_svg]:w-5 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
                    @include('icons.whatsapp')
                </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700 dark:text-slate-300">واتساپ</span>
            </a>

            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}" target="_blank"
               rel="nofollow noopener"
               class="group flex flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-blue-200 hover:bg-blue-50 sm:rounded-3xl sm:p-4 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/10">
                <span
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:bg-white sm:h-11 sm:w-11 dark:bg-blue-500/10 dark:text-blue-300 dark:group-hover:bg-blue-500/15 [&_svg]:h-5 [&_svg]:w-5 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
                    @include('icons.linkedin-in')
                </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700 dark:text-slate-300">لینکدین</span>
            </a>

            <button type="button" x-on:click="nativeShare()"
                    class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-brand-200 hover:bg-brand-50 sm:rounded-3xl sm:p-4 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">
                <span
                    class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-50 text-brand-500 transition group-hover:bg-white sm:h-11 sm:w-11 dark:bg-brand-500/10 dark:text-brand-300 dark:group-hover:bg-brand-500/15 [&_svg]:h-5 [&_svg]:w-5 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
                    @include('icons.share-08')
                </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700 dark:text-slate-300">اشتراک</span>
            </button>
        </div>
    </div>
</x-front.share-modal>
