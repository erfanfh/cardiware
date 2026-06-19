@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">
        @include('front.marketplace.stores.partials.detail.hero')

        <div
            id="products"
            class="mt-4 grid scroll-mt-28 grid-cols-1 gap-4 sm:mt-6 sm:grid-cols-2 sm:gap-5 md:grid-cols-3 lg:grid-cols-4 lg:gap-6 lg:scroll-mt-36
            dark:text-slate-200"
        >
            @forelse($storeProducts as $product)
                <x-front.product.card :product="$product"/>
            @empty
                <div
                    class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-6 sm:py-16
                    dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl sm:h-16 sm:w-16
                        dark:bg-slate-800">
                        🛒
                    </div>

                    <h3 class="mt-5 text-base font-black text-slate-950 sm:text-lg dark:text-slate-100">
                        محصولی یافت نشد!
                    </h3>

                    <p class="mt-2 text-xs leading-6 text-slate-500 sm:text-sm dark:text-slate-400">
                        این فروشنده هنوز محصولی ثبت نکرده است.
                    </p>
                </div>
            @endforelse
        </div>

        @if($storeProducts->hasPages())
            <div class="mt-5 sm:mt-6">
                {{ $storeProducts->links() }}
            </div>
        @endif
    </main>
@endsection
