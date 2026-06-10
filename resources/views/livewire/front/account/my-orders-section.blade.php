<section id="my-orders-section" class="scroll-mt-36 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-1">
        <div class="text-base font-extrabold text-slate-900">سفارش‌های من</div>
        <div class="text-xs text-slate-500">تاریخچه سفارش‌ها، پرداخت‌ها و فاکتورها به ترتیب آخرین سفارش</div>
    </div>

    <div class="mt-5 space-y-4 transition" wire:loading.class="opacity-40">
        @forelse($this->orders as $order)
            <article
                x-data="{ open: false }"
                wire:key="order-{{ $order->id }}"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition"
            >
                <button
                    type="button"
                    @click="open = !open"
                    class="block w-full p-4 text-right cursor-pointer"
                >
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-xl border px-2 py-1 text-[11px] font-extrabold {{ $this->orderStatusClass($order) }}">
                                    {{ $this->orderStatusLabel($order) }}
                                </span>

                                <span
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-extrabold text-slate-600">
                                    {{ $this->dateLabel($order->created_at) }}
                                </span>

                                @if($order->isPaid())
                                    <span
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-extrabold text-emerald-700">
                                        پرداخت: {{ $this->dateTimeLabel($order->paid_at) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-col gap-1">
                                <div class="text-sm font-black text-slate-900 sm:text-base">
                                    سفارش {{ $order->order_number }}
                                </div>

                                <div class="text-sm font-extrabold leading-7 text-slate-700">
                                    {{ $this->orderTitle($order) }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-start gap-2 lg:items-end">
                            <div class="text-lg font-black text-slate-900">
                                {{ $this->money($order->total_amount) }}
                            </div>

                            <div class="inline-flex items-center gap-1 text-xs font-extrabold text-brand-700">
                                <span x-text="open ? 'بستن جزئیات' : 'مشاهده جزئیات'"></span>
                                <svg
                                    class="h-4 w-4 transition"
                                    :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <path d="m6 9 6 6 6-6"
                                          stroke="currentColor"
                                          stroke-width="2"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </button>

                <div
                    x-show="open"
                    x-collapse
                    class="border-t border-slate-100 bg-slate-50/60"
                >
                    <div class="grid gap-4 p-4 xl:grid-cols-[1fr_320px]">
                        <div class="space-y-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 text-sm font-black text-slate-900">آیتم‌های سفارش</div>

                                <div class="space-y-3">
                                    @forelse($order->rootItems as $item)
                                        <div
                                            wire:key="order-{{ $order->id }}-item-{{ $item->id }}"
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-3"
                                        >
                                            <div
                                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-extrabold leading-7 text-slate-900">
                                                        {{ $this->orderItemTitle($item) }}
                                                    </div>

                                                    @if($this->orderItemDescription($item))
                                                        <div class="mt-1 text-xs font-bold leading-6 text-slate-500">
                                                            {{ $this->orderItemDescription($item) }}
                                                        </div>
                                                    @endif

                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <span
                                                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[11px] font-extrabold text-slate-600">
                                                            {{ $this->quantityLabel($item) }}
                                                        </span>

                                                        <span
                                                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[11px] font-extrabold text-slate-600">
                                                            قیمت واحد: {{ $this->money($item->unit_price) }}
                                                        </span>

                                                        @if($item->discount_amount > 0)
                                                            <span
                                                                class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-extrabold text-emerald-700">
                                                                تخفیف: {{ $this->money($item->discount_amount) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-sm font-black text-slate-900">
                                                    {{ $this->money($item->total_amount) }}
                                                </div>
                                            </div>

                                            @if($this->hasChildren($item))
                                                <div class="mt-3 space-y-2 border-t border-slate-200 pt-3">
                                                    @foreach($item->children as $child)
                                                        <div
                                                            wire:key="order-{{ $order->id }}-child-{{ $child->id }}"
                                                            class="flex flex-col gap-2 rounded-xl bg-white p-3 sm:flex-row sm:items-center sm:justify-between"
                                                        >
                                                            <div class="text-xs font-extrabold text-slate-700">
                                                                {{ $this->orderItemTitle($child) }}
                                                            </div>

                                                            <div class="text-xs font-black text-slate-900">
                                                                {{ $this->money($child->total_amount) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div
                                            class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-bold text-slate-500">
                                            آیتمی برای این سفارش ثبت نشده است.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <aside class="space-y-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 text-sm font-black text-slate-900">خلاصه سفارش</div>

                                <div class="space-y-3 text-xs font-bold text-slate-600">
                                    <div class="flex items-center justify-between gap-3">
                                        <span>شماره سفارش</span>
                                        <span class="font-black text-slate-900">{{ $order->order_number }}</span>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span>تاریخ ثبت</span>
                                        <span
                                            class="font-black text-slate-900">{{ $this->dateTimeLabel($order->created_at) }}</span>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span>وضعیت سفارش</span>
                                        <span
                                            class="rounded-xl border px-2 py-1 text-[11px] font-extrabold {{ $this->orderStatusClass($order) }}">
                                            {{ $this->orderStatusLabel($order) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span>آخرین پرداخت</span>
                                        <span
                                            class="font-black text-slate-900">{{ $this->latestPaymentLabel($order) }}</span>
                                    </div>

                                    <div class="border-t border-slate-100 pt-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>جمع سفارش</span>
                                            <span
                                                class="font-black text-slate-900">{{ $this->money($order->subtotal_amount) }}</span>
                                        </div>

                                        @if($order->discount_amount > 0)
                                            <div class="mt-3 flex items-center justify-between gap-3">
                                                <span>تخفیف</span>
                                                <span
                                                    class="font-black text-emerald-700">{{ $this->money($order->discount_amount) }}</span>
                                            </div>
                                        @endif

                                        <div class="mt-3 flex items-center justify-between gap-3">
                                            <span class="text-slate-900">مبلغ نهایی</span>
                                            <span
                                                class="text-base font-black text-slate-900">{{ $this->money($order->total_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="mb-3 text-sm font-black text-slate-900">عملیات</div>

                                <div class="flex flex-col gap-2">
                                    @if($this->canRetryPayment($order))
                                        <a
                                            href="{{ $this->retryPaymentUrl($order) }}"
                                            class="inline-flex h-11 items-center justify-center rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600"
                                        >
                                            پرداخت سفارش
                                        </a>
                                    @endif

                                    @if($this->hasDownloads($order))
                                        <a
                                            href="{{ $this->downloadsUrl() }}"
                                            class="inline-flex h-11 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 px-4 text-sm font-extrabold text-brand-800 transition hover:bg-brand-100"
                                        >
                                            مشاهده دانلودها
                                        </a>
                                    @endif

                                    @if($this->hasInvoiceRoute())
                                        <a
                                            href="{{ $this->invoiceUrl($order) }}"
                                            class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                                        >
                                            دریافت فاکتور
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <div
                    class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-slate-50 text-slate-400 ring-1 ring-slate-200">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M8 7h8M8 12h8M8 17h5M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="mt-4 text-sm font-extrabold text-slate-800">
                    سفارشی یافت نشد
                </div>

                <p class="mt-2 text-xs leading-6 text-slate-500">
                    بعد از ثبت اولین سفارش، تاریخچه خریدهای شما در این بخش نمایش داده می‌شود.
                </p>
            </div>
        @endforelse

        @if($this->orders->hasPages())
            <div class="pt-2">
                {{ $this->orders->links(data: ['scrollTo' => '#my-orders-section']) }}
            </div>
        @endif
    </div>
</section>
