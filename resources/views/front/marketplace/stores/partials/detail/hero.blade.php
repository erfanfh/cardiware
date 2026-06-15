<x-front.cart class="relative overflow-hidden !p-0">
    <div class="absolute inset-0 bg-gradient-to-l from-brand-50 via-white to-white"></div>
    <div class="absolute -top-10 -left-10 h-32 w-32 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
    <div class="absolute top-6 left-8 h-3 w-3 rounded-full bg-brand-500"></div>

    <div class="relative p-5 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Seller main info -->
            <div class="lg:col-span-8">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-5">
                    <div class="shrink-0">
                        <div class="rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[3px] shadow-sm">
                            <div class="h-24 w-24 sm:h-28 sm:w-28 rounded-full bg-white p-1">
                                <img
                                    src="{{ $store->picture?->url }}"
                                    alt="{{ $store->title }}"
                                    class="h-full w-full rounded-full object-cover"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                                فروشنده تاییدشده
                            </span>

                            @if($store->products->count() >= 3)
                                <span
                                    class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">
                                    فعال در مودینگ وی
                                </span>
                            @endif
                        </div>

                        <h1 class="mt-3 text-2xl font-black leading-tight text-slate-900">
                            فروشگاه {{ $store->title }}
                        </h1>

                        <div class="mt-3 text-sm text-slate-600 leading-7 max-w-3xl">
                            {!! $store->body !!}
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <a href="#products"
                               class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm hover:bg-brand-600 active:bg-brand-700 transition">
                                مشاهده همه محصولات
                                <span class="[&_svg]:w-4 [&_svg]:h-4">
                                    @include('icons.arrow-left-02')
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick meta card -->
            <div class="lg:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <div class="text-sm font-extrabold text-slate-900">اطلاعات فروشنده</div>

                    <div class="mt-4 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                تاریخ عضویت
                            </span>
                            <span
                                class="font-extrabold text-slate-900">{{ jalali_date($store->created_at, 'd F Y') }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                تعداد محصول
                            </span>
                            <span class="font-extrabold text-slate-900">{{ $store->products_count }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                تعداد فروش
                            </span>
                            <span class="font-extrabold text-slate-900">{{ $soldQuantity }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-front.cart>
