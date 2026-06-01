<template x-if="isVariableDigitalProduct">
    <div class="overflow-hidden rounded-2xl border border-slate-200">
        <div class="px-4 py-4">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">آیتم‌های فروش</h4>
                    <p class="mt-1 text-xs leading-6 text-slate-500">
                        هر آیتم فروش، یک حالت قابل فروش از محصول است.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.form.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        class="cursor-pointer"
                        @click="addSaleItem()"
                    >
                        <span class="[&_svg]:w-3 [&_svg]:h-3">
                            @include('icons.plus-sign')
                        </span>
                        <span>افزودن آیتم</span>
                    </x-admin.form.button>

                    <x-admin.form.button
                        type="button"
                        variant="soft"
                        size="sm"
                        class="cursor-pointer"
                        @click="generateAllSaleItemCombinations()"
                        x-bind:disabled="saleItemVariationAttributes.length === 0"
                    >
                        <span>ساخت همه حالت‌ها</span>
                    </x-admin.form.button>
                </div>
            </div>

            <template x-if="saleItemVariationAttributes.length === 0">
                <div
                    class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">
                    برای ساخت آیتم فروش بر اساس ویژگی‌ها، حداقل یک ویژگی در تب «ویژگی‌ها» باید گزینه «استفاده برای آیتم
                    فروش» داشته باشد.
                </div>
            </template>

            <template x-if="saleItems.length === 0">
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-7 text-center">
                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-400">
                        <span class="text-lg leading-none">✦</span>
                    </div>

                    <div class="mt-3 text-sm font-semibold text-slate-700">
                        هنوز آیتم فروشی ساخته نشده است.
                    </div>

                    <div class="mx-auto mt-1 max-w-xl text-xs leading-6 text-slate-500">
                        آیتم فروش را دستی اضافه کنید یا از دکمه ساخت همه حالت‌ها استفاده کنید.
                    </div>
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="(item, i) in saleItems" :key="item.__key">
                    @include('admin.catalog.products.partials.information.pricing.sale-item')
                </template>
            </div>
        </div>
    </div>
</template>
