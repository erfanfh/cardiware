<section
    id="my-favorites-section"
    class="scroll-mt-36 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
>
    <div class="border-b border-slate-100 p-4 sm:p-5 dark:border-slate-800">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <span
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-600 ring-1 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20 [&_svg]:h-5 [&_svg]:w-5">
                        @include('icons.favourite')
                    </span>

                    <div class="min-w-0">
                        <div class="text-sm font-black text-slate-900 sm:text-base dark:text-white">
                            Favorites
                        </div>
                        <div class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            Products you saved for later
                        </div>
                    </div>
                </div>
            </div>

            <span
                class="inline-flex w-fit items-center rounded-xl border border-rose-100 bg-rose-50 px-2.5 py-1 text-[11px] font-extrabold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                {{ number_format($this->favorites->total()) }} items
            </span>
        </div>
    </div>

    <div class="relative p-4 sm:p-5">
        <div
            wire:loading.flex
            wire:target="remove, gotoPage, previousPage, nextPage"
            class="absolute inset-0 z-30 hidden items-center justify-center rounded-b-2xl bg-white/60 backdrop-blur-[2px] sm:rounded-b-3xl dark:bg-slate-900/60"
        >
            <div
                class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-extrabold text-slate-700 shadow-lg dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <span
                    class="h-4 w-4 animate-spin rounded-full border-2 border-brand-200 border-t-brand-600 dark:border-brand-500/20 dark:border-t-brand-300"></span>
                Updating
            </div>
        </div>

        @if($message)
            <div
                wire:key="favorites-message-{{ $messageVersion }}"
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 3200)"
                x-show="show"
                x-transition.opacity
                class="mb-4 flex items-start gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold leading-6 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
            >
                <span class="mt-0.5 shrink-0 [&_svg]:h-4 [&_svg]:w-4">@include('icons.favourite')</span>
                <span class="min-w-0 flex-1">{{ $message }}</span>
                <button type="button" x-on:click="show = false"
                        class="cursor-pointer text-current opacity-60 transition hover:opacity-100 [&_svg]:h-4 [&_svg]:w-4"
                        aria-label="Dismiss message">
                    @include('icons.cancel-01')
                </button>
            </div>
        @endif

        @if($this->favorites->isNotEmpty())
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:gap-5 2xl:grid-cols-3">
                @foreach($this->favorites as $product)
                    <div
                        wire:key="favorite-product-{{ $product->id }}"
                        class="group/favorite-card relative min-w-0"
                    >
                        <x-front.product.card :product="$product"/>

                        <button
                            type="button"
                            wire:click="remove({{ $product->id }})"
                            wire:loading.attr="disabled"
                            wire:target="remove({{ $product->id }})"
                            class="cursor-pointer absolute left-2.5 top-2.5 z-20 inline-flex h-9 items-center gap-1.5 rounded-xl border border-white/70 bg-white/95 px-2.5 text-[11px] font-extrabold text-rose-600 shadow-lg shadow-slate-950/10 backdrop-blur transition hover:bg-rose-50 disabled:cursor-wait disabled:opacity-70 sm:left-3 sm:top-3 dark:border-slate-700 dark:bg-slate-900/95 dark:text-rose-300 dark:hover:bg-rose-500/10 [&_svg]:h-4 [&_svg]:w-4"
                            aria-label="Remove {{ $product->title }} from favorites"
                        >
                            <span wire:loading.remove wire:target="remove({{ $product->id }})">
                                @include('icons.favourite')
                            </span>
                            <span wire:loading wire:target="remove({{ $product->id }})"
                                  class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-rose-200 border-t-rose-600 dark:border-rose-500/20 dark:border-t-rose-300"></span>
                            Remove
                        </button>
                    </div>
                @endforeach
            </div>

            @if($this->favorites->hasPages())
                <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                    {{ $this->favorites->onEachSide(1)->links(data: ['scrollTo' => '#my-favorites-section']) }}
                </div>
            @endif
        @else
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-5 text-center sm:rounded-3xl sm:p-8 dark:border-slate-700 dark:bg-slate-800/45">
                <div
                    class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-white text-rose-500 shadow-sm ring-1 ring-rose-100 sm:h-16 sm:w-16 dark:bg-slate-900 dark:text-rose-300 dark:ring-rose-500/20 [&_svg]:h-7 [&_svg]:w-7 sm:[&_svg]:h-8 sm:[&_svg]:w-8">
                    @include('icons.favourite')
                </div>

                <div class="mt-4 text-sm font-black leading-7 text-slate-900 sm:text-base dark:text-white">
                    You have not saved any products yet
                </div>

                <p class="mx-auto mt-2 max-w-xl text-xs font-medium leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                    Use the heart button on any product page to add it to this list and quickly
                    return to it whenever needed.
                </p>

                <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                    <span
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                        Save for later
                    </span>
                    <span
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                        Quick access from account
                    </span>
                </div>

                <div class="mt-6">
                    <a
                        href="{{ route('products.list') }}"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 sm:w-auto dark:bg-brand-500 dark:hover:bg-brand-400 [&_svg]:h-4 [&_svg]:w-4"
                    >
                        View products
                        <x-front.arrow-right-02/>
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>
