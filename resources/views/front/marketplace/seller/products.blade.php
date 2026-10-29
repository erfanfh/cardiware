@extends('front.marketplace.seller.layout')

@section('seller-title', 'محصولات')
@section('seller-heading', 'محصولات فروشگاه')
@section('seller-description', 'محصولات و گزارش‌های در دسترس فروشگاه را ببینید.')

@section('seller-content')
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 sm:p-7 dark:border-slate-800">
            <div><h2 class="text-lg font-black">فهرست محصولات</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($products->total()) }} محصول</p></div>
            <form method="get" class="flex w-full gap-2 sm:w-auto">
                <input name="q" value="{{ $search }}" type="search" placeholder="جست‌وجوی نام محصول" aria-label="جست‌وجوی محصول" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-brand-400 sm:w-64 dark:border-slate-700 dark:bg-slate-800">
                <button class="cursor-pointer rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white">جست‌وجو</button>
            </form>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($products as $product)
                @php($sale = $sales->get($product->id))
                <div class="grid gap-4 p-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-6">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="min-w-0 break-words text-sm font-black sm:text-base">{{ $product->title }}</h3>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $product->isPubliclyVisible() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $product->isPubliclyVisible() ? 'منتشرشده' : 'منتشرنشده' }}</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            @if($store->sellerCan('view_commission'))
                                <span>کمیسیون فروشگاه: {{ number_format($product->seller_commission_basis_points / 100, 2) }}٪</span>
                            @endif
                            @if($store->sellerCan('view_sales_count'))
                            <span>سفارش: {{ number_format((int) ($sale->orders_count ?? 0)) }}</span>
                            <span>تعداد فروش: {{ number_format((int) ($sale->units_count ?? 0)) }}</span>
                            <span>افزونه فروخته‌شده: {{ number_format((int) ($sale->addon_units_count ?? 0)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex min-w-0 flex-wrap items-center gap-3 sm:justify-end">
                        @if($store->sellerCan('view_revenue'))
                            <div class="w-full min-w-0 sm:w-auto"><div class="text-[11px] text-slate-500 dark:text-slate-400">سهم فروشگاه از محصول و افزونه</div><div class="mt-1 break-all text-sm font-black text-emerald-600 dark:text-emerald-400">{{ number_format((int) ($sale->seller_amount ?? 0)) }} تومان</div></div>
                        @endif
                        @if($store->sellerCan('view_sales_details'))
                        <a href="{{ route('seller.stores.products.sales', [$store, $product->id]) }}" class="rounded-xl bg-brand-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-brand-700">جزئیات فروش</a>
                        @endif
                        @if($product->isPubliclyVisible())
                            <a href="{{ $product->url }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-brand-600 hover:border-brand-300 dark:border-slate-700 dark:text-brand-300">نمایش محصول</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">محصولی با این مشخصات پیدا نشد.</div>
            @endforelse
        </div>
        @if($products->hasPages())<div class="border-t border-slate-100 p-5 dark:border-slate-800">{{ $products->links() }}</div>@endif
    </section>
@endsection
