@php $url = url()->current(); @endphp
<x-front.share-modal name="product-share-{{ $product->id }}" max-width="max-w-xl">
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
                    title: '{{ $product->title }}',
                    text: '{{ $product->excerpt() ?: $product->title }}',
                    url: '{{ $url }}'
                });
            }
        }
    }"
        class="p-5 sm:p-6"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <div
                    class="inline-flex items-center gap-2 rounded-2xl bg-brand-50 px-3 py-1.5 text-xs font-extrabold text-brand-600 ring-1 ring-brand-100">
                    اشتراک گذاری
                </div>

                <h3 class="mt-4 text-lg sm:text-xl font-black text-slate-900 leading-8">
                    این صفحه را با دیگران به اشتراک بگذارید
                </h3>

                <p class="mt-2 text-sm leading-7 text-slate-500">
                    لینک محصول را کپی کنید یا از طریق شبکه های اجتماعی ارسال کنید.
                </p>
            </div>

            <button
                type="button"
                x-on:click="$dispatch('close-modal')"
                class="cursor-pointer shrink-0 h-10 w-10 [&_svg]:w-5 [&_svg]:h-5 rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition grid place-items-center"
                aria-label="بستن"
            >
                @include('icons.cancel-01')
            </button>
        </div>

        <div class="mt-5 rounded-3xl bg-slate-50 border border-slate-200 p-3">
            <div class="flex items-center gap-2">
                <input
                    x-ref="shareInput"
                    type="text"
                    readonly
                    value="{{ $url }}"
                    class="w-full h-11 rounded-2xl border border-slate-200 bg-white px-3 text-left direction-ltr text-sm text-slate-600 outline-none focus:ring-2 focus:ring-brand-100"
                >

                <button
                    type="button"
                    x-on:click="copyLink()"
                    class="cursor-pointer shrink-0 h-11 px-4 rounded-2xl bg-slate-900 text-white text-sm font-extrabold hover:bg-slate-800 transition"
                >
                    <span x-show="!copied">کپی</span>
                    <span x-show="copied" x-cloak>کپی شد</span>
                </button>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <a
                href="https://t.me/share/url?url={{ urlencode($url) }}&text={{ urlencode($product->title) }}"
                target="_blank"
                rel="nofollow noopener"
                class="group flex flex-col items-center justify-center gap-2 rounded-3xl border border-slate-200 bg-white p-4 hover:border-sky-200 hover:bg-sky-50 transition"
            >
            <span
                class="h-11 w-11 [&_svg]:w-6 [&_svg]:h-6 rounded-2xl bg-sky-50 text-sky-500 group-hover:bg-white transition grid place-items-center">
                @include('icons.telegram')
            </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700">تلگرام</span>
            </a>

            <a
                href="https://wa.me/?text={{ urlencode($product->title . ' ' . $url) }}"
                target="_blank"
                rel="nofollow noopener"
                class="group flex flex-col items-center justify-center gap-2 rounded-3xl border border-slate-200 bg-white p-4 hover:border-emerald-200 hover:bg-emerald-50 transition"
            >
            <span
                class="h-11 w-11 [&_svg]:w-6 [&_svg]:h-6 rounded-2xl bg-emerald-50 text-emerald-500 group-hover:bg-white transition grid place-items-center">
                @include('icons.whatsapp')
            </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700">واتساپ</span>
            </a>

            <a
                href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}"
                target="_blank"
                rel="nofollow noopener"
                class="group flex flex-col items-center justify-center gap-2 rounded-3xl border border-slate-200 bg-white p-4 hover:border-blue-200 hover:bg-blue-50 transition"
            >
            <span
                class="h-11 w-11 [&_svg]:w-6 [&_svg]:h-6 rounded-2xl bg-blue-50 text-blue-600 group-hover:bg-white transition grid place-items-center">
                @include('icons.linkedin-in')
            </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700">لینکدین</span>
            </a>

            <button
                type="button"
                x-on:click="nativeShare()"
                class="cursor-pointer group flex flex-col items-center justify-center gap-2 rounded-3xl border border-slate-200 bg-white p-4 hover:border-brand-200 hover:bg-brand-50 transition"
            >
            <span
                class="h-11 w-11 [&_svg]:w-6 [&_svg]:h-6 rounded-2xl bg-brand-50 text-brand-500 group-hover:bg-white transition grid place-items-center">
                @include('icons.share-08')
            </span>
                <span class="mt-1 text-xs font-extrabold text-slate-700">اشتراک</span>
            </button>
        </div>
    </div>
</x-front.share-modal>
