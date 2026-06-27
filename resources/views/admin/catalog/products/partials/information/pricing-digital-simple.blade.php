<template x-if="isSimpleDigitalProduct">
    <div class="overflow-hidden rounded-2xl border border-slate-200">
        <input
            type="hidden"
            name="sale_items[0][id]"
            value="{{ old('sale_items.0.id', $simpleSaleItem['id'] ?? null) }}"
        >

        <div class="px-4 py-4">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">تنظیمات فروش</h4>
                </div>

                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                    آیتم فروش پیش‌فرض
                </span>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="قیمت پایه (تومان)">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][base_price]"
                            :value="old('sale_items.0.base_price', $simpleSaleItem['base_price'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="وضعیت فروش">
                        <x-admin.form.select
                            name="sale_items[0][status]"
                            :value="old('sale_items.0.status', $simpleSaleItem['status'] ?? 'available')"
                            :options="[
                                'available' => 'موجود',
                                'unavailable' => 'ناموجود',
                            ]"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4"></div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="حداکثر تعداد در هر سفارش" hint="برای نامحدود، خالی بذارید.">
                        <x-admin.form.input
                            type="number"
                            min="1"
                            name="sale_items[0][max_quantity_per_order]"
                            :value="old('sale_items.0.max_quantity_per_order', $simpleSaleItem['max_quantity_per_order'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="محدودیت دانلود (تعداد)" hint="برای نامحدود، خالی بگذارید.">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][download_limit]"
                            :value="old('sale_items.0.download_limit', $simpleSaleItem['download_limit'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="انقضای لینک دانلود (روز)" hint="برای نامحدود، خالی بگذارید.">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][download_expires_in_days]"
                            :value="old('sale_items.0.download_expires_in_days', $simpleSaleItem['download_expires_in_days'] ?? null)"
                        />
                    </x-admin.field>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-4">
            <input
                type="hidden"
                name="sale_items[0][discount][id]"
                value="{{ old('sale_items.0.discount.id', $simpleSaleItemDiscount['id'] ?? null) }}"
            >

            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">تخفیف ویژه</h4>
                        </div>
                    </div>
                </div>

                <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-bold text-orange-700">
                    اختیاری
                </span>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="مبلغ با تخفیف (تومان)" hint="اگر خالی باشد، تخفیفی اعمال نمی‌شود.">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][discount][discounted_price]"
                            :value="old('sale_items.0.discount.discounted_price', $simpleSaleItemDiscount['discounted_price'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="زمان شروع">
                        <x-admin.form.date-picker
                            type="datetime-local"
                            name="sale_items[0][discount][starts_at]"
                            :value="old('sale_items.0.discount.starts_at', $simpleSaleItemDiscount['starts_at'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="زمان پایان">
                        <x-admin.form.date-picker
                            type="datetime-local"
                            name="sale_items[0][discount][ends_at]"
                            :value="old('sale_items.0.discount.ends_at', $simpleSaleItemDiscount['ends_at'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12">
                    <input
                        type="hidden"
                        name="sale_items[0][discount][show_special_box]"
                        value="0"
                    >

                    <label
                        class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-orange-200">
                        <input
                            type="checkbox"
                            name="sale_items[0][discount][show_special_box]"
                            value="1"
                            @checked((bool) old('sale_items.0.discount.show_special_box', $simpleSaleItemDiscount['show_special_box'] ?? false))
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                        >

                        <span>
                        <span class="block text-xs font-bold text-slate-800">
                            نمایش باکس تخفیف ویژه
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-slate-500">
                            اگر فعال باشد، در فرانت محصول می‌توانید یک باکس تبلیغاتی برای این تخفیف نمایش دهید.
                        </span>
                    </span>
                    </label>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <x-admin.field label="عنوان باکس تخفیف ویژه">
                        <x-admin.form.input
                            name="sale_items[0][discount][special_box_title]"
                            :value="old('sale_items.0.discount.special_box_title', $simpleSaleItemDiscount['special_box_title'] ?? null)"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <x-admin.field label="توضیحات باکس تخفیف ویژه">
                        <x-admin.form.textarea
                            rows="2"
                            name="sale_items[0][discount][special_box_description]"
                        >{{ old('sale_items.0.discount.special_box_description', $simpleSaleItemDiscount['special_box_description'] ?? null) }}</x-admin.form.textarea>
                    </x-admin.field>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">فایل‌های دانلودی</h4>
                </div>

                <button
                    type="button"
                    class="inline-flex h-8 cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-orange-200 hover:text-orange-600"
                    @click="addSimpleFile()"
                >
                    <span class="text-sm leading-none">+</span>
                    <span>افزودن فایل</span>
                </button>
            </div>

            <template x-if="simpleFiles.length === 0">
                <div class="rounded-xl border border-dashed border-slate-300 px-3 py-2 text-xs text-slate-500">
                    هنوز فایلی برای دانلود اضافه نشده است.
                </div>
            </template>

            <div class="space-y-2">
                <template x-for="(file, fileIndex) in simpleFiles" :key="file.__key">
                    <div class="flex flex-col gap-2 rounded-xl border border-slate-200 p-2 xl:flex-row xl:items-center">
                        <input
                            type="hidden"
                            x-bind:name="`sale_items[0][files][${fileIndex}][id]`"
                            x-model="file.id"
                        >

                        <input
                            type="hidden"
                            x-bind:name="`sale_items[0][files][${fileIndex}][sort_order]`"
                            x-bind:value="fileIndex"
                        >

                        <div class="xl:w-4/12">
                            <x-admin.form.input
                                x-bind:name="`sale_items[0][files][${fileIndex}][title]`"
                                x-model="file.title"
                                placeholder="عنوان فایل"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <x-admin.form.input
                                x-bind:name="`sale_items[0][files][${fileIndex}][url]`"
                                x-model="file.url"
                                class="direction-ltr"
                                placeholder="https://..."
                            />
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 hover:text-rose-700 xl:w-24"
                            @click="removeSimpleFile(fileIndex)"
                        >
                            حذف
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
