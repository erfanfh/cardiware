@php
    $boxTitle = get_setting('products', 'first_box_title') ?: 'جدیدترین محصولات';
    $boxSubtitle = get_setting('products', 'first_box_subtitle');
    if(request()->routeIs('products.categories') and isset($category)){
        $boxTitle = $category->title;
        $boxSubtitle = "جدیدترین محصولات دسته $category->title";
    }elseif(request()->routeIs('products.tags') and isset($tag)){
        $boxTitle = $tag->title;
        $boxSubtitle = "جدیدترین محصولات برچسب $tag->title";
    }
@endphp

<div class="flex flex-col gap-6">
    @include('livewire.front.catalog.partials.product-list-slider')

    @include('livewire.front.catalog.partials.product-list-title')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        @include('livewire.front.catalog.partials.product-list-filters')

        {{-- Products --}}
        <main class="lg:col-span-9">
            <x-front.cart
                class="mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-950">
                        لیست محصولات
                    </h3>

                    <p class="mt-1 text-xs font-medium text-slate-400">
                        مرتب‌سازی و مشاهده محصولات قابل خرید
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-xs font-black text-slate-400">
                        مرتب‌سازی:
                    </label>

                    <select
                        wire:model.live="sort"
                        class="cursor-pointer h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                    >
                        <option value="latest">جدیدترین</option>
                        <option value="oldest">قدیمی‌ترین</option>
                        <option value="price_asc">ارزان‌ترین</option>
                        <option value="price_desc">گران‌ترین</option>
                    </select>
                </div>
            </x-front.cart>

            <div wire:loading.class="opacity-50 pointer-events-none" class="transition">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($products as $product)
                        <x-front.product.card :product="$product"/>
                    @empty
                        <div
                            class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                            <div
                                class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-2xl">
                                🛒
                            </div>

                            <h3 class="mt-5 text-lg font-black text-slate-950">
                                محصولی با این فیلترها پیدا نشد
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                فیلترها را تغییر بده یا همه فیلترها را حذف کن.
                            </p>

                            <button
                                type="button"
                                wire:click="resetFilters"
                                class="mt-5 inline-flex h-11 items-center justify-center rounded-2xl bg-brand-600 px-5 text-sm font-black text-white transition hover:bg-brand-700"
                            >
                                حذف فیلترها
                            </button>
                        </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
