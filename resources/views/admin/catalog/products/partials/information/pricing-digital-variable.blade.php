<template x-if="isVariableDigitalProduct">
    <div class="space-y-5">
        <div class="rounded-2xl border border-dashed border-slate-200 p-4">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-800">تنظیمات کلی فروش محصول متغیر</h4>
                    <p class="mt-1 text-xs text-slate-500">
                        این تنظیمات روی کل محصول متغیر اعمال می‌شود.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="حداکثر تعداد در هر سفارش">
                        <x-admin.form.input
                            type="number"
                            min="1"
                            name="variable_max_quantity_per_order"
                            :value="old('variable_max_quantity_per_order')"
                            placeholder="مثلا 1"
                        />
                    </x-admin.field>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-dashed border-slate-200 p-4">
            <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-slate-800">آیتم‌های فروش</h4>
                    <p class="mt-1 text-xs leading-6 text-slate-500">
                        هر آیتم فروش، یک حالت قابل فروش از محصول است؛ مثلا نسخه PC یا ترکیب PC / Full.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-admin.form.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="addSaleItem()"
                    >
                        <span class="[&_svg]:w-3 [&_svg]:h-3">
                            @include('icons.plus-sign')
                        </span>
                        <span>افزودن آیتم فروش</span>
                    </x-admin.form.button>

                    <x-admin.form.button
                        type="button"
                        variant="soft"
                        size="sm"
                        @click="generateAllSaleItemCombinations()"
                        x-bind:disabled="saleItemVariationAttributes.length === 0"
                    >
                        <span>ساخت همه حالت‌ها</span>
                    </x-admin.form.button>
                </div>
            </div>

            <template x-if="saleItemVariationAttributes.length === 0">
                <div
                    class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-6 text-amber-700">
                    برای ساخت آیتم فروش بر اساس ویژگی‌ها، حداقل یک ویژگی در تب «ویژگی‌ها» باید گزینه «استفاده
                    برای آیتم فروش» داشته باشد و مقدار هم داشته باشد.
                </div>
            </template>

            <template x-if="saleItems.length === 0">
                <div
                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-8 text-center">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                        <span class="text-xl leading-none">✦</span>
                    </div>

                    <div class="mt-3 text-sm font-semibold text-slate-700">
                        هنوز آیتم فروشی برای این محصول ساخته نشده است.
                    </div>

                    <div class="mx-auto mt-1 max-w-xl text-xs leading-6 text-slate-500">
                        می‌توانید آیتم فروش را دستی اضافه کنید یا بعد از تعیین ویژگی‌های قابل استفاده در آیتم
                        فروش، همه حالت‌ها را بسازید.
                    </div>
                </div>
            </template>

            <div class="space-y-4">
                <template x-for="(item, i) in saleItems" :key="item.__key">
                    <div class="rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300">
                        <div
                            class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3.5"
                            @click="item._open = !item._open"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div
                                        class="truncate text-sm font-bold text-slate-800 max-w-[520px]"
                                        x-text="saleItemHeading(item, i)"
                                    ></div>

                                    <span
                                        class="rounded-full px-2.5 py-1 text-[11px] font-medium"
                                        :class="item.status === 'available'
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-rose-50 text-rose-700'"
                                        x-text="item.status === 'available' ? 'موجود' : 'ناموجود'"
                                    ></span>
                                </div>

                                <div
                                    class="mt-1 text-[11px] text-slate-500 truncate max-w-[620px]"
                                    x-text="saleItemCombinationLabel(item) || 'بدون ترکیب ویژگی'"
                                ></div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                                    @click.stop="removeSaleItem(i)"
                                    title="حذف"
                                >
                                    <span class="text-sm leading-none">✕</span>
                                </button>

                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition">
                                    <span x-show="item._open" x-cloak class="[&_svg]:w-3 [&_svg]:h-3">
                                        @include('icons.arrow-down-01')
                                    </span>
                                    <span x-show="!item._open" x-cloak class="[&_svg]:w-3 [&_svg]:h-3">
                                        @include('icons.arrow-right-01')
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-t border-slate-100 bg-slate-50/50 px-4 py-4"
                            x-show="item._open"
                            x-cloak
                        >
                            <input
                                type="hidden"
                                x-bind:name="`sale_items[${i}][id]`"
                                x-model="item.id"
                            >

                            <input
                                type="hidden"
                                x-bind:name="`sale_items[${i}][sort_order]`"
                                x-bind:value="i"
                            >

                            <div class="space-y-5">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="mb-4">
                                        <h5 class="text-sm font-bold text-slate-800">ترکیب ویژگی‌ها</h5>
                                        <p class="mt-1 text-xs text-slate-500">
                                            مشخص کنید این آیتم فروش مربوط به کدام مقدار از ویژگی‌هاست.
                                        </p>
                                    </div>

                                    <template x-if="saleItemVariationAttributes.length === 0">
                                        <div
                                            class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">
                                            هنوز ویژگی قابل استفاده برای آیتم فروش انتخاب نشده است.
                                        </div>
                                    </template>

                                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                                        <template x-for="variationAttribute in saleItemVariationAttributes"
                                                  :key="variationAttribute.key">
                                            <div>
                                                <x-admin.field label="">
                                                    <div class="mb-1.5 text-xs font-semibold text-slate-700"
                                                         x-text="variationAttribute.title"></div>

                                                    <select
                                                        class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300"
                                                        x-bind:name="`sale_items[${i}][attribute_values][${variationAttribute.key}]`"
                                                        x-model="item.attribute_values[variationAttribute.key]"
                                                    >
                                                        <option value="">انتخاب کنید</option>

                                                        <template x-for="value in variationAttribute.values"
                                                                  :key="value.key">
                                                            <option
                                                                :value="value.key"
                                                                x-text="value.title"
                                                            ></option>
                                                        </template>
                                                    </select>
                                                </x-admin.field>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="mb-4">
                                        <h5 class="text-sm font-bold text-slate-800">اطلاعات فروش</h5>
                                    </div>

                                    <div class="grid grid-cols-12 gap-5">
                                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                                            <x-admin.field label="شناسه آیتم فروش">
                                                <x-admin.form.input
                                                    x-bind:name="`sale_items[${i}][sku]`"
                                                    x-model="item.sku"
                                                    class="direction-ltr"
                                                    placeholder="مثلا MW-FC25-PC"
                                                />
                                            </x-admin.field>
                                        </div>

                                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                                            <x-admin.field label="عنوان نمایشی">
                                                <x-admin.form.input
                                                    x-bind:name="`sale_items[${i}][title]`"
                                                    x-model="item.title"
                                                    placeholder="مثلا نسخه PC"
                                                />
                                            </x-admin.field>
                                        </div>

                                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                                            <x-admin.field label="قیمت پایه">
                                                <x-admin.form.input
                                                    type="number"
                                                    min="0"
                                                    x-bind:name="`sale_items[${i}][base_price]`"
                                                    x-model="item.base_price"
                                                    placeholder="مثلا 250000"
                                                />
                                            </x-admin.field>
                                        </div>

                                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                                            <x-admin.field label="وضعیت فروش">
                                                <select
                                                    x-bind:name="`sale_items[${i}][status]`"
                                                    x-model="item.status"
                                                    class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300"
                                                >
                                                    <option value="available">موجود</option>
                                                    <option value="unavailable">ناموجود</option>
                                                </select>
                                            </x-admin.field>
                                        </div>

                                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                                            <x-admin.field label="محدودیت دانلود">
                                                <x-admin.form.input
                                                    type="number"
                                                    min="0"
                                                    x-bind:name="`sale_items[${i}][download_limit]`"
                                                    x-model="item.download_limit"
                                                    placeholder="مثلا 5"
                                                />
                                            </x-admin.field>
                                        </div>

                                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                                            <x-admin.field label="انقضای لینک دانلود">
                                                <x-admin.form.input
                                                    type="number"
                                                    min="0"
                                                    x-bind:name="`sale_items[${i}][download_expires_in_days]`"
                                                    x-model="item.download_expires_in_days"
                                                    placeholder="تعداد روز، مثلا 7"
                                                />
                                            </x-admin.field>
                                        </div>

                                        <div class="col-span-12">
                                            <x-admin.field label="توضیحات آیتم فروش">
                                                <x-admin.form.textarea
                                                    rows="3"
                                                    x-bind:name="`sale_items[${i}][description]`"
                                                    x-model="item.description"
                                                    placeholder="توضیح کوتاه درباره این آیتم فروش..."
                                                />
                                            </x-admin.field>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-800">فایل‌های دانلودی</h5>
                                            <p class="mt-1 text-xs text-slate-500">
                                                لینک‌های دانلود مربوط به همین آیتم فروش.
                                            </p>
                                        </div>

                                        <x-admin.form.button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            @click="addSaleItemFile(item)"
                                        >
                                            <span class="[&_svg]:w-3 [&_svg]:h-3">
                                                @include('icons.plus-sign')
                                            </span>
                                            <span>افزودن فایل</span>
                                        </x-admin.form.button>
                                    </div>

                                    <template x-if="item.files.length === 0">
                                        <div
                                            class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-xs text-slate-500">
                                            هنوز فایلی برای این آیتم فروش اضافه نشده است.
                                        </div>
                                    </template>

                                    <div class="space-y-2">
                                        <template x-for="(file, fileIndex) in item.files" :key="file.__key">
                                            <div
                                                class="grid grid-cols-12 gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                                                <input
                                                    type="hidden"
                                                    x-bind:name="`sale_items[${i}][files][${fileIndex}][id]`"
                                                    x-model="file.id"
                                                >

                                                <input
                                                    type="hidden"
                                                    x-bind:name="`sale_items[${i}][files][${fileIndex}][sort_order]`"
                                                    x-bind:value="fileIndex"
                                                >

                                                <div class="col-span-12 xl:col-span-4">
                                                    <x-admin.form.input
                                                        x-bind:name="`sale_items[${i}][files][${fileIndex}][title]`"
                                                        x-model="file.title"
                                                        placeholder="عنوان فایل"
                                                    />
                                                </div>

                                                <div class="col-span-12 xl:col-span-6">
                                                    <x-admin.form.input
                                                        x-bind:name="`sale_items[${i}][files][${fileIndex}][url]`"
                                                        x-model="file.url"
                                                        class="direction-ltr"
                                                        placeholder="https://..."
                                                    />
                                                </div>

                                                <div class="col-span-12 xl:col-span-2">
                                                    <button
                                                        type="button"
                                                        class="flex h-10 w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                                                        @click="removeSaleItemFile(item, fileIndex)"
                                                    >
                                                        حذف
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
