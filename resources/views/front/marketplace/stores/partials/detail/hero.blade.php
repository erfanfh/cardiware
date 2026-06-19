<x-front.cart class="relative overflow-hidden !p-0">
    <div class="absolute inset-0 bg-gradient-to-b from-brand-50 via-white to-white sm:bg-gradient-to-l"></div>
    <div class="absolute -left-10 -top-10 h-28 w-28 rounded-[44px] bg-brand-100/70 blur-[1px] sm:h-32 sm:w-32"></div>
    <div class="absolute left-6 top-5 h-2.5 w-2.5 rounded-full bg-brand-500 sm:left-8 sm:top-6 sm:h-3 sm:w-3"></div>

    <div class="relative p-4 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-12 lg:gap-6">
            <div class="min-w-0 lg:col-span-8">
                <div class="relative">
                    <div class="h-28 rounded-3xl bg-gradient-to-l from-brand-500 to-brand-400 sm:h-32"></div>

                    <div class="relative -mt-14 px-4 pb-4 sm:-mt-16 sm:px-5">
                        <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-end sm:gap-5">
                            <div class="shrink-0 rounded-full bg-white p-1.5 shadow-sm ring-4 ring-white">
                                <div class="h-24 w-24 overflow-hidden rounded-full bg-slate-100 sm:h-28 sm:w-28">
                                    <img
                                        src="{{ $store->picture?->url }}"
                                        alt="{{ $store->title }}"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                            </div>

                            <div class="min-w-0 flex-1 text-center sm:pb-2 sm:text-right">
                                <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                                    <span
                                        class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[11px] font-extrabold text-brand-800">
                                        فروشنده تاییدشده
                                    </span>

                                    @if($store->products->count() >= 3)
                                        <span
                                            class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-extrabold text-emerald-700">
                                            فعال در مودینگ وی
                                        </span>
                                    @endif
                                </div>

                                <h1 class="mt-3 text-xl font-black leading-8 text-slate-900 sm:text-2xl">
                                    فروشگاه {{ $store->title }}
                                </h1>
                            </div>
                        </div>

                        <div class="mx-auto mt-4 max-w-3xl text-center text-sm leading-7 text-slate-600 sm:text-right">
                            {!! $store->body !!}
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
                            <a
                                href="#products"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-600 active:bg-brand-700"
                            >
                                مشاهده همه محصولات
                                <span class="[&_svg]:h-4 [&_svg]:w-4">
                                    @include('icons.arrow-left-02')
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="min-w-0 lg:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <div class="text-sm font-extrabold text-slate-900">
                        اطلاعات فروشنده
                    </div>

                    <div class="mt-4 space-y-3 text-sm sm:space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5 text-xs text-slate-500 sm:text-sm">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                تاریخ عضویت
                            </span>

                            <span class="text-left text-xs font-extrabold text-slate-900 sm:text-sm">
                                {{ jalali_date($store->created_at, 'd F Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5 text-xs text-slate-500 sm:text-sm">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                تعداد محصول
                            </span>

                            <span class="text-left text-xs font-extrabold text-slate-900 sm:text-sm">
                                {{ $store->products_count }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-1.5 text-xs text-slate-500 sm:text-sm">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                تعداد فروش
                            </span>

                            <span class="text-left text-xs font-extrabold text-slate-900 sm:text-sm">
                                {{ $soldQuantity }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-front.cart>
