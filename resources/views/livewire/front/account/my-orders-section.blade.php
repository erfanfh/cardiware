<section id="my-orders-section"
         class="scroll-mt-36 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
    <div class="flex flex-col gap-1">
        <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">سفارش‌های من</div>
        <div class="text-xs leading-5 text-slate-500 dark:text-slate-400">تاریخچه سفارش‌ها، پرداخت‌ها و فاکتورها به
            ترتیب آخرین سفارش
        </div>
    </div>

    <div class="mt-4 space-y-3 transition sm:mt-5 sm:space-y-4" wire:loading.class="opacity-40">
        @forelse($this->orders as $order)
            <article
                x-data="{ open: false }"
                wire:key="order-{{ $order->id }}"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800/70 dark:shadow-black/20"
            >
                <button type="button" @click="open = !open" class="block w-full cursor-pointer p-3 text-right sm:p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-xl border px-2 py-1 text-[10px] font-extrabold sm:text-[11px] {{ $this->orderStatusClass($order) }}">
                                    {{ $this->orderStatusLabel($order) }}
                                </span>

                                <span
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-extrabold text-slate-600 sm:text-[11px] dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                                    {{ $this->dateLabel($order->created_at) }}
                                </span>

                                @if($order->isPaid())
                                    <span
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700 sm:text-[11px] dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                        پرداخت: {{ $this->dateTimeLabel($order->paid_at) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-col gap-1">
                                <div class="text-sm font-black text-slate-900 sm:text-base dark:text-white">
                                    سفارش {{ $order->order_number }}
                                </div>

                                <div
                                    class="line-clamp-2 text-xs font-extrabold leading-6 text-slate-700 sm:text-sm sm:leading-7 dark:text-slate-300">
                                    {{ $this->orderTitle($order) }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row items-center justify-between gap-3 lg:flex-col lg:items-end">
                            <div class="text-base font-black text-slate-900 sm:text-lg dark:text-white">
                                {{ $this->money($order->total_amount) }}
                            </div>

                            <div
                                class="inline-flex items-center gap-1 text-xs font-extrabold text-brand-700 dark:text-brand-300">
                                <span x-text="open ? 'بستن جزئیات' : 'مشاهده جزئیات'"></span>
                                <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24"
                                     fill="none">
                                    <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </button>

                <div x-show="open" x-collapse
                     class="border-t border-slate-100 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-900/50">
                    <div class="grid gap-3 p-3 sm:p-4 xl:grid-cols-[minmax(0,1fr)_320px] xl:gap-4">
                        <div class="min-w-0 space-y-3">
                            <div
                                class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/80">
                                <div class="mb-3 text-sm font-black text-slate-900 dark:text-white">آیتم‌های سفارش</div>

                                <div class="space-y-3">
                                    @forelse($order->rootItems as $item)
                                        <div wire:key="order-{{ $order->id }}-item-{{ $item->id }}"
                                             class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                                            <div
                                                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="min-w-0">
                                                    <div
                                                        class="text-sm font-extrabold leading-7 text-slate-900 dark:text-white">
                                                        {{ $this->orderItemTitle($item) }}
                                                    </div>

                                                    @if($this->orderItemDescription($item))
                                                        <div
                                                            class="mt-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                                                            {{ $this->orderItemDescription($item) }}
                                                        </div>
                                                    @endif

                                                    <div class="mt-2 flex flex-wrap gap-2">
                                                        <span
                                                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[10px] font-extrabold text-slate-600 sm:text-[11px] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                            {{ $this->quantityLabel($item) }}
                                                        </span>

                                                        <span
                                                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[10px] font-extrabold text-slate-600 sm:text-[11px] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                            قیمت واحد: {{ $this->money($item->unit_price) }}
                                                        </span>

                                                        @if($item->discount_amount > 0)
                                                            <span
                                                                class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700 sm:text-[11px] dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                                تخفیف: {{ $this->money($item->discount_amount) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-sm font-black text-slate-900 dark:text-white">
                                                    {{ $this->money($item->total_amount) }}
                                                </div>
                                            </div>

                                            @if($this->hasChildren($item))
                                                <div
                                                    class="mt-3 space-y-2 border-t border-slate-200 pt-3 dark:border-slate-700">
                                                    @foreach($item->children as $child)
                                                        <div wire:key="order-{{ $order->id }}-child-{{ $child->id }}"
                                                             class="flex flex-col gap-2 rounded-xl bg-white p-3 sm:flex-row sm:items-center sm:justify-between dark:bg-slate-800">
                                                            <div
                                                                class="text-xs font-extrabold leading-5 text-slate-700 dark:text-slate-300">
                                                                {{ $this->orderItemTitle($child) }}
                                                            </div>

                                                            <div
                                                                class="text-xs font-black text-slate-900 dark:text-white">
                                                                {{ $this->money($child->total_amount) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div
                                            class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm font-bold text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                                            آیتمی برای این سفارش ثبت نشده است.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @if($order->descriptions->isNotEmpty())
                                <div
                                    class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/80">
                                    <div class="mb-3 text-sm font-black text-slate-900 dark:text-white">توضیحات سفارش</div>

                                    <div class="space-y-3">
                                        @foreach($order->descriptions as $description)
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                                                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                                    {{ $this->dateTimeLabel($description->created_at) }}
                                                    @if($description->is_legacy)
                                                        <span class="mr-1">(تاریخ تقریبی؛ توضیح قدیمی)</span>
                                                    @endif
                                                </div>
                                                <div class="mt-2 whitespace-pre-wrap break-words text-[0.8rem] leading-7 text-slate-700 dark:text-gray-100">{{ $description->body }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <aside class="min-w-0 space-y-3">
                            <div
                                class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/80">
                                <div class="mb-3 text-sm font-black text-slate-900 dark:text-white">خلاصه سفارش</div>

                                <div class="space-y-3 text-xs font-bold text-slate-600 dark:text-slate-400">
                                    <div class="flex items-center justify-between gap-3">
                                        <span>شماره سفارش</span>
                                        <span
                                            class="font-black text-slate-900 dark:text-white">{{ $order->order_number }}</span>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span>تاریخ ثبت</span>
                                        <span
                                            class="text-left font-black text-slate-900 dark:text-white">{{ $this->dateTimeLabel($order->created_at) }}</span>
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
                                            class="text-left font-black text-slate-900 dark:text-white">{{ $this->latestPaymentLabel($order) }}</span>
                                    </div>

                                    <div class="border-t border-slate-100 pt-3 dark:border-slate-700">
                                        <div class="flex items-center justify-between gap-3">
                                            <span>جمع سفارش</span>
                                            <span
                                                class="font-black text-slate-900 dark:text-white">{{ $this->money($order->subtotal_amount) }}</span>
                                        </div>

                                        @if($order->discount_amount > 0)
                                            <div class="mt-3 flex items-center justify-between gap-3">
                                                <span>تخفیف</span>
                                                <span
                                                    class="font-black text-emerald-700 dark:text-emerald-300">{{ $this->money($order->discount_amount) }}</span>
                                            </div>
                                        @endif

                                        @if((int) $order->tax_amount > 0)
                                            <div class="mt-3 flex items-center justify-between gap-3">
                                                <span>مالیات ({{ rtrim(rtrim(number_format($order->tax_rate_basis_points / 100, 2, '.', ''), '0'), '.') }}٪)</span>
                                                <span class="font-black text-amber-700 dark:text-amber-300">{{ $this->money($order->tax_amount) }}</span>
                                            </div>
                                        @endif

                                        <div class="mt-3 flex items-center justify-between gap-3">
                                            <span class="text-slate-900 dark:text-white">مبلغ نهایی</span>
                                            <span
                                                class="text-base font-black text-slate-900 dark:text-white">{{ $this->money($order->total_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/80">
                                <div class="mb-3 text-sm font-black text-slate-900 dark:text-white">عملیات</div>

                                <div class="flex flex-col gap-2">
                                    @if($this->hasDownloads($order))
                                        <a href="{{ $this->downloadsUrl() }}"
                                           class="inline-flex h-11 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 px-4 text-sm font-extrabold text-brand-800 transition hover:bg-brand-100 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15">
                                            مشاهده دانلودها
                                        </a>
                                    @endif

                                    @if($this->hasInvoiceRoute())
                                        <a href="{{ $this->invoiceUrl($order) }}"
                                           class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
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
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center sm:rounded-3xl sm:p-8 dark:border-slate-700 dark:bg-slate-800/70">
                <div
                    class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-slate-50 text-slate-400 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-500 dark:ring-slate-700">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M8 7h8M8 12h8M8 17h5M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="mt-4 text-sm font-extrabold text-slate-800 dark:text-white">سفارشی یافت نشد</div>

                <p class="mx-auto mt-2 max-w-sm text-xs leading-6 text-slate-500 dark:text-slate-400">
                    بعد از ثبت اولین سفارش، تاریخچه خریدهای شما در این بخش نمایش داده می‌شود.
                </p>
            </div>
        @endforelse

        @if($this->orders->hasPages())
            <div class="pt-2 dark:text-slate-300">
                {{ $this->orders->links(data: ['scrollTo' => '#my-orders-section']) }}
            </div>
        @endif
    </div>
</section>
