@php
    /*
     * This relation should be Query product page eager load defined:
     *
     * activeMedals.picture
     */
    $productMedals = $product->activeMedals;
@endphp

@if($productMedals->isNotEmpty())
    <x-front.cart
        id="product-medals"
        class="overflow-visible border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90"
    >
        <div
            class="flex flex-wrap justify-center gap-3"
            role="list"
            aria-label="Product medals"
        >
            @foreach($productMedals as $medal)
                <div
                    role="listitem"
                    class="group relative min-w-0 flex-none
                        w-[calc((100%-3.75rem)/6)]
                        sm:w-[calc((100%-5.25rem)/8)]
                        lg:w-[calc((100%-3rem)/5)]"
                >
                    <div
                        tabindex="0"
                        aria-label="{{ $medal->title }}"
                        class="relative aspect-square w-full cursor-default overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm outline-none transition duration-200 hover:-translate-y-1 hover:border-brand-200 hover:shadow-md focus:-translate-y-1 focus:border-brand-300 focus:ring-2 focus:ring-brand-200 focus:ring-offset-2 focus:ring-offset-white dark:border-slate-700 dark:bg-slate-800 dark:hover:border-brand-500/40 dark:focus:border-brand-500/50 dark:focus:ring-brand-500/30 dark:focus:ring-offset-slate-900"
                    >
                        @if($medal->picture?->url)
                            <img
                                src="{{ $medal->picture->url }}"
                                alt=""
                                class="h-full w-full rounded-[inherit] object-cover transition duration-300 group-hover:scale-105 group-focus-within:scale-105"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <div
                                class="flex h-full w-full items-center justify-center bg-slate-100 dark:bg-slate-800"
                            >
                                <svg
                                    class="size-8 text-slate-300 dark:text-slate-600"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.362 5.214A8.252 8.252 0 0 1 12 21a8.25 8.25 0 0 1-3.362-15.786m6.724 0a3 3 0 1 0-6.724 0m6.724 0A3 3 0 0 1 18 8.25c0 1.182-.684 2.204-1.678 2.694m-7.684-5.73A3 3 0 0 0 6 8.25c0 1.182.684 2.204 1.678 2.694m0 0a4.5 4.5 0 0 0 8.644 0m-8.644 0A4.474 4.474 0 0 0 7.5 12.25a4.5 4.5 0 0 0 9 0c0-.45-.066-.884-.178-1.306"
                                    />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Tooltip --}}
                    <div
                        role="tooltip"
                        class="pointer-events-none invisible absolute bottom-full left-1/2 z-50 mb-3 w-max max-w-40 -translate-x-1/2 translate-y-1 rounded-xl bg-slate-950 px-3 py-2 text-center text-[11px] font-extrabold leading-5 text-white opacity-0 shadow-xl transition duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100 dark:bg-white dark:text-slate-950"
                    >
                        {{ $medal->title }}

                        <span
                            class="absolute left-1/2 top-full size-2 -translate-x-1/2 -translate-y-1/2 rotate-45 bg-slate-950 dark:bg-white"
                            aria-hidden="true"
                        ></span>
                    </div>
                </div>
            @endforeach
        </div>
    </x-front.cart>
@endif
