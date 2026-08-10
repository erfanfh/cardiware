<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300">
    <div class="px-4 py-3">
        <div class="grid grid-cols-12 items-center gap-3">
            <div class="col-span-12 xl:col-span-7">
                <template x-if="saleItemVariationAttributes.length === 0">
                    <div
                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">
                        هنوز ویژگی قابل استفاده برای آیتم فروش انتخاب نشده است.
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <template x-for="variationAttribute in saleItemVariationAttributes" :key="variationAttribute.key">
                        <div>
                            <div
                                class="mb-1 text-[11px] font-semibold text-slate-600"
                                x-text="variationAttribute.title"
                            ></div>

                            <select
                                class="h-10 w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-200"
                                x-bind:name="`sale_items[${i}][attribute_values][${variationAttribute.key}]`"
                                x-bind:value="item.attribute_values[variationAttribute.key] ?? ''"
                                @click.stop
                                @change.stop="item.attribute_values[variationAttribute.key] = $event.target.value"
                            >
                                <option value="">انتخاب کنید</option>

                                <template x-for="value in variationAttribute.values" :key="value.key">
                                    <option
                                        x-bind:value="value.key"
                                        x-bind:selected="String(item.attribute_values[variationAttribute.key] ?? '') === String(value.key)"
                                        x-text="value.title"
                                    ></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                        :class="item.status === 'available'
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-rose-50 text-rose-700'"
                        x-text="item.status === 'available' ? 'موجود' : 'ناموجود'"
                    ></span>

                    <span
                        class="max-w-full truncate rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"
                        x-text="saleItemCombinationLabel(item) || 'بدون ترکیب'"
                    ></span>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-2">
                <div class="flex items-center gap-2 xl:justify-end">
                    <button
                        type="button"
                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                        @click.stop="removeSaleItem(i)"
                        title="حذف"
                    >
                        <span class="text-sm leading-none">✕</span>
                    </button>

                    <button
                        type="button"
                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100"
                        @click="item._open = !item._open"
                    >
                        <span x-show="item._open" x-cloak class="[&_svg]:h-3 [&_svg]:w-3">
                            @include('icons.arrow-down-01')
                        </span>
                        <span x-show="!item._open" x-cloak class="[&_svg]:h-3 [&_svg]:w-3">
                            @include('icons.arrow-right-01')
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        class="border-t border-slate-200 bg-white"
        x-show="item._open"
        x-cloak
    >
        <input type="hidden" x-bind:name="`sale_items[${i}][id]`" x-model="item.id">
        <input type="hidden" x-bind:name="`sale_items[${i}][sort_order]`" x-bind:value="i">

        <div class="px-4 py-3">
            <div class="mb-2 text-xs font-bold text-slate-700">اطلاعات فروش</div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="عنوان آیتم فروش">
                        <x-admin.form.input
                            x-bind:name="`sale_items[${i}][title]`"
                            x-model="item.title"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="کلید نسخه">
                        <x-admin.form.input
                            x-bind:name="`sale_items[${i}][edition_key]`"
                            x-model="item.edition_key"
                            class="direction-ltr"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="نسخه فعلی">
                        <x-admin.form.input
                            x-bind:name="`sale_items[${i}][version]`"
                            x-model="item.version"
                            class="direction-ltr"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="شناسه آیتم فروش">
                        <x-admin.form.input
                            x-bind:name="`sale_items[${i}][sku]`"
                            x-model="item.sku"
                            class="direction-ltr"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="قیمت پایه (تومان)">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            x-bind:name="`sale_items[${i}][base_price]`"
                            x-model="item.base_price"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="وضعیت فروش">
                        <select
                            x-bind:name="`sale_items[${i}][status]`"
                            x-model="item.status"
                            class="h-10 w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-200"
                        >
                            <option value="available">موجود</option>
                            <option value="unavailable">ناموجود</option>
                        </select>
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="حداکثر تعداد در هر سفارش" hint="برای نامحدود، خالی بذارید.">
                        <x-admin.form.input
                            type="number"
                            min="1"
                            x-bind:name="`sale_items[${i}][max_quantity_per_order]`"
                            x-model="item.max_quantity_per_order"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="محدودیت دانلود (تعداد)" hint="برای نامحدود، خالی بگذارید.">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            x-bind:name="`sale_items[${i}][download_limit]`"
                            x-model="item.download_limit"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="انقضای لینک دانلود (روز)" hint="برای نامحدود، خالی بگذارید.">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            x-bind:name="`sale_items[${i}][download_expires_in_days]`"
                            x-model="item.download_expires_in_days"
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

        <div class="border-t border-slate-200 px-4 py-3">
            <input
                type="hidden"
                x-bind:name="`sale_items[${i}][discount][id]`"
                x-model="item.discount.id"
            >

            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <div>
                            <div class="text-xs font-bold text-slate-800">تخفیف ویژه</div>
                        </div>
                    </div>
                </div>

                <span
                    class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                    :class="item.discount.discounted_price
                ? 'bg-orange-100 text-orange-700'
                : 'bg-slate-100 text-slate-500'"
                    x-text="item.discount.discounted_price ? 'تخفیف فعال' : 'بدون تخفیف'"
                ></span>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="مبلغ با تخفیف (تومان)" hint="اگر خالی باشد، تخفیفی اعمال نمی‌شود.">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            x-bind:name="`sale_items[${i}][discount][discounted_price]`"
                            x-model="item.discount.discounted_price"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="زمان شروع">
                        <x-admin.form.date-picker
                            type="datetime-local"
                            x-bind:name="`sale_items[${i}][discount][starts_at]`"
                            x-model="item.discount.starts_at"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="زمان پایان">
                        <x-admin.form.date-picker
                            type="datetime-local"
                            x-bind:name="`sale_items[${i}][discount][ends_at]`"
                            x-model="item.discount.ends_at"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12">
                    <input
                        type="hidden"
                        x-bind:name="`sale_items[${i}][discount][show_special_box]`"
                        value="0"
                    >

                    <label
                        class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 transition hover:border-orange-200 hover:bg-orange-50/40">
                        <input
                            type="checkbox"
                            x-bind:name="`sale_items[${i}][discount][show_special_box]`"
                            value="1"
                            x-model="item.discount.show_special_box"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                        >

                        <span>
                        <span class="block text-xs font-bold text-slate-800">
                            نمایش باکس تخفیف ویژه
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-slate-500">
                            این گزینه فقط برای کنترل نمایش باکس تبلیغاتی تخفیف در فرانت محصول استفاده می‌شود.
                        </span>
                    </span>
                    </label>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <x-admin.field label="عنوان باکس تخفیف ویژه">
                        <x-admin.form.input
                            x-bind:name="`sale_items[${i}][discount][special_box_title]`"
                            x-model="item.discount.special_box_title"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <x-admin.field label="توضیحات باکس تخفیف ویژه">
                        <x-admin.form.textarea
                            rows="2"
                            x-bind:name="`sale_items[${i}][discount][special_box_description]`"
                            x-model="item.discount.special_box_description"
                        />
                    </x-admin.field>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-700">فایل‌های دانلودی</div>

                <button
                    type="button"
                    class="inline-flex h-8 cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-orange-200 hover:text-orange-600"
                    @click="addSaleItemFile(item)"
                >
                    <span class="text-sm leading-none">+</span>
                    <span>افزودن فایل</span>
                </button>
            </div>

            <template x-if="item.files.length === 0">
                <div class="rounded-xl border border-dashed border-slate-300 px-3 py-2 text-xs text-slate-500">
                    هنوز فایلی برای این آیتم فروش اضافه نشده است.
                </div>
            </template>

            <div class="space-y-2">
                <template x-for="(file, fileIndex) in item.files" :key="file.__key">
                    <div class="grid grid-cols-12 items-end gap-2 rounded-xl border border-slate-200 p-2">
                        <input type="hidden" x-bind:name="`sale_items[${i}][files][${fileIndex}][id]`"
                               x-model="file.id">
                        <input type="hidden" x-bind:name="`sale_items[${i}][files][${fileIndex}][sort_order]`"
                               x-bind:value="fileIndex">

                        <div class="col-span-12 md:col-span-6 xl:col-span-3">
                            <x-admin.form.input
                                x-bind:name="`sale_items[${i}][files][${fileIndex}][title]`"
                                x-model="file.title"
                                placeholder="عنوان فایل"
                            />
                        </div>

                        <div class="col-span-12 md:col-span-6 xl:col-span-5">
                            <x-admin.form.input
                                x-bind:name="`sale_items[${i}][files][${fileIndex}][url]`"
                                x-model="file.url"
                                class="direction-ltr"
                                placeholder="https://..."
                            />
                        </div>

                        <div class="col-span-12 md:col-span-6 xl:col-span-4">
                            <x-admin.form.input
                                x-bind:name="`sale_items[${i}][files][${fileIndex}][file_name]`"
                                x-model="file.file_name"
                                class="direction-ltr"
                                placeholder="product.7z یا product.7z.001"
                            />
                        </div>

                        <div class="col-span-6 md:col-span-3 xl:col-span-3">
                            <x-admin.form.input type="number" min="1"
                                x-bind:name="`sale_items[${i}][files][${fileIndex}][size_bytes]`"
                                x-model="file.size_bytes"
                                placeholder="حجم (Byte)"
                            />
                        </div>

                        <div class="col-span-12 md:col-span-6 xl:col-span-5">
                            <x-admin.form.input
                                x-bind:name="`sale_items[${i}][files][${fileIndex}][sha256]`"
                                x-model="file.sha256"
                                class="direction-ltr"
                                placeholder="SHA-256"
                            />
                        </div>

                        <div class="col-span-6 md:col-span-3 xl:col-span-2">
                            <x-admin.form.input type="number" min="1"
                                x-bind:name="`sale_items[${i}][files][${fileIndex}][part_number]`"
                                x-model="file.part_number"
                                placeholder="پارت"
                            />
                        </div>
                        <input type="hidden" x-bind:name="`sale_items[${i}][files][${fileIndex}][is_required]`" value="1">
                        <input type="hidden" x-bind:name="`sale_items[${i}][files][${fileIndex}][is_active]`" value="1">

                        <button
                            type="button"
                            class="col-span-12 inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 hover:text-rose-700 md:col-span-3 xl:col-span-2"
                            @click="removeSaleItemFile(item, fileIndex)"
                        >
                            حذف
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">تنظیمات لانچر</h4>
                </div>

                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700">
                    Manifest
                </span>
            </div>

            <x-admin.catalog.product-sale-item-manifest-fields
                mode="variable"
            />
            <div class="mt-4">
                <x-admin.catalog.launcher-content-fields mode="variable"/>
            </div>
        </div>
    </div>
</div>
