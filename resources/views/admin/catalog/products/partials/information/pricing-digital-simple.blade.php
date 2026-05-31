<template x-if="isSimpleDigitalProduct">
    <div class="space-y-5">
        <input type="hidden" name="sale_items[0][id]"
               value="{{ old('sale_items.0.id', $simpleSaleItem['id'] ?? null) }}">

        <div class="rounded-2xl border border-dashed border-slate-200 p-4">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-800">تنظیمات فروش محصول ساده</h4>
                    <p class="mt-1 text-xs text-slate-500">
                        این محصول یک آیتم فروش پیش‌فرض خواهد داشت.
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                            آیتم فروش پیش‌فرض
                        </span>
            </div>

            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="قیمت پایه">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][base_price]"
                            :value="old('sale_items.0.base_price', $simpleSaleItem['base_price'] ?? null)"
                            placeholder="مثلا 250000"
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

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="حداکثر تعداد در هر سفارش">
                        <x-admin.form.input
                            type="number"
                            min="1"
                            name="sale_items[0][max_quantity_per_order]"
                            :value="old('sale_items.0.max_quantity_per_order', $simpleSaleItem['max_quantity_per_order'] ?? null)"
                            placeholder="مثلا 1"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="محدودیت دانلود">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][download_limit]"
                            :value="old('sale_items.0.download_limit', $simpleSaleItem['download_limit'] ?? null)"
                            placeholder="مثلا 5"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-6 xl:col-span-4">
                    <x-admin.field label="انقضای لینک دانلود">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            name="sale_items[0][download_expires_in_days]"
                            :value="old('sale_items.0.download_expires_in_days', $simpleSaleItem['download_expires_in_days'] ?? null)"
                            placeholder="تعداد روز، مثلا 7"
                        />
                    </x-admin.field>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-dashed border-slate-200 p-4">
            <x-admin.form.repeater
                name="sale_items[0][files]"
                :items="$simpleSaleItemFiles"
                :defaultItem="[
                            'id' => null,
                            'title' => '',
                            'url' => '',
                        ]"
                title="فایل‌های دانلودی"
                addText="افزودن فایل"
                emptyText="هنوز فایلی برای دانلود اضافه نشده است."
                headingKey="title"
                headingPlaceholder="فایل جدید"
            >
                <div class="space-y-4">
                    <input
                        type="hidden"
                        x-bind:name="field(i, 'id')"
                        x-model="it.id"
                    >

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <x-admin.field label="عنوان فایل">
                            <x-admin.form.input
                                x-bind:name="field(i, 'title')"
                                x-model="it.title"
                                placeholder="مثلا فایل اصلی محصول"
                            />
                        </x-admin.field>

                        <x-admin.field label="لینک فایل">
                            <x-admin.form.input
                                x-bind:name="field(i, 'url')"
                                x-model="it.url"
                                class="direction-ltr"
                                placeholder="https://..."
                            />
                        </x-admin.field>
                    </div>
                </div>
            </x-admin.form.repeater>
        </div>
    </div>
</template>
