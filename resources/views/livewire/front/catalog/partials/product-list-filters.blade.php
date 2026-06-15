<aside class="lg:col-span-3">
    <div class="sticky top-6 space-y-4">
        <x-front.cart class="overflow-hidden !p-0">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-base font-black text-slate-950">
                        فیلتر محصولات
                    </h3>

                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="cursor-pointer text-xs font-bold text-brand-600 transition hover:text-brand-700"
                    >
                        حذف فیلترها
                    </button>
                </div>
            </div>

            <div class="space-y-7 p-5">
                {{-- Price Range --}}
                <div
                    x-data="{
                        min: @entangle('priceMin').live,
                        max: @entangle('priceMax').live,
                        floor: {{ $availablePriceMin }},
                        ceil: {{ $availablePriceMax }},
                        format(value) {
                            return new Intl.NumberFormat('fa-IR').format(value)
                        },
                        minPercent() {
                            return ((this.min - this.floor) / (this.ceil - this.floor || 1)) * 100
                        },
                        maxPercent() {
                            return ((this.max - this.floor) / (this.ceil - this.floor || 1)) * 100
                        },
                        fixMin() {
                            this.min = Number(this.min)

                            if (this.min > this.max) {
                                this.min = this.max
                            }

                            if (this.min < this.floor) {
                                this.min = this.floor
                            }
                        },
                        fixMax() {
                            this.max = Number(this.max)

                            if (this.max < this.min) {
                                this.max = this.min
                            }

                            if (this.max > this.ceil) {
                                this.max = this.ceil
                            }
                        }
                    }"
                    class="rounded-2xl bg-slate-100/85 border border-slate-200/85 p-4"
                >
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black text-slate-900">
                                محدوده قیمت
                            </h4>
                        </div>

                        <span class="rounded-2xl bg-brand-50 px-3 py-1 text-[11px] font-black text-brand-600">
                            تومان
                        </span>
                    </div>

                    <div class="mb-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                            <span class="block text-[11px] font-bold text-slate-400">
                                کمترین
                            </span>

                            <strong
                                class="mt-1 block text-sm font-black text-slate-900"
                                x-text="format(min)"
                            ></strong>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                            <span class="block text-[11px] font-bold text-slate-400">
                                بیشترین
                            </span>

                            <strong
                                class="mt-1 block text-sm font-black text-slate-900"
                                x-text="format(max)"
                            ></strong>
                        </div>
                    </div>

                    <div class="relative h-10">
                        <div
                            class="absolute left-0 right-0 top-1/2 h-2 -translate-y-1/2 rounded-full bg-slate-200"></div>

                        <div
                            class="absolute top-1/2 h-2 -translate-y-1/2 rounded-full bg-brand-600"
                            :style="`right: ${minPercent()}%; left: ${100 - maxPercent()}%`"
                        ></div>

                        <input
                            type="range"
                            :min="floor"
                            :max="ceil"
                            step="1000"
                            x-model="min"
                            x-on:input="fixMin()"
                            class="pointer-events-none absolute inset-x-0 top-1/2 h-2 w-full -translate-y-1/2 appearance-none bg-transparent [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-4 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:bg-brand-600 [&::-webkit-slider-thumb]:shadow-lg">

                        <input
                            type="range"
                            :min="floor"
                            :max="ceil"
                            step="1000"
                            x-model="max"
                            x-on:input="fixMax()"
                            class="pointer-events-none absolute inset-x-0 top-1/2 h-2 w-full -translate-y-1/2 appearance-none bg-transparent [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-4 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:bg-brand-600 [&::-webkit-slider-thumb]:shadow-lg">
                    </div>

                    <div class="mt-3 flex items-center justify-between text-[11px] font-bold text-slate-400">
                        <span>{{ number_format($availablePriceMin) }}</span>
                        <span>{{ number_format($availablePriceMax) }}</span>
                    </div>
                </div>

                {{-- Categories --}}
                <div>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black text-slate-900">
                            دسته‌بندی‌ها
                        </h4>

                        @if(! empty($selectedCategories))
                            <span class="rounded-2xl bg-brand-50 px-2.5 py-1 text-[11px] font-black text-brand-600">
                                {{ count($selectedCategories) }} انتخاب
                            </span>
                        @endif
                    </div>

                    <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                        @forelse($categories as $category)
                            @include('livewire.front.catalog.partials.category-filter-tree', [
                                'categories' => collect([$category]),
                                'level' => 0,
                                'selectedCategories' => $selectedCategories,
                            ])
                        @empty
                            <div class="rounded-2xl bg-slate-50 px-4 py-5 text-center text-xs font-bold text-slate-400">
                                دسته‌بندی‌ای یافت نشد.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-front.cart>
    </div>
</aside>
