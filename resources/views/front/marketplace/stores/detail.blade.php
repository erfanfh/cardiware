@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.marketplace.stores.partials.detail.hero')

        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @forelse($storeProducts as $product)
                <x-front.product.card :product="$product"/>
            @empty
                <div
                    class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <div
                        class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-2xl">
                        🛒
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-950">
                        محصولی یافت نشد!
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        این فروشنده هنوز محصولی ثبت نکرده است.
                    </p>
                </div>
            @endforelse
        </div>

        @if($storeProducts->hasPages())
            <div class="mt-6">
                {{ $storeProducts->links() }}
            </div>
        @endif
    </main>
@endsection
