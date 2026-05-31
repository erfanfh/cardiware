<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300">
    <div
        class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3"
        @click="attribute._open = !attribute._open"
    >
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <div
                    class="truncate text-sm font-extrabold text-slate-900"
                    x-text="attribute.title && String(attribute.title).trim().length ? attribute.title : 'ویژگی اختصاصی جدید'"
                    :class="!attribute.title || !String(attribute.title).trim().length ? 'text-slate-400 font-semibold' : ''"
                ></div>

                <span class="inline-flex rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700">
                    اختصاصی
                </span>

                <span
                    class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"
                    x-text="attribute.values.length + ' مقدار'"
                ></span>
            </div>

            <div class="mt-1 text-[11px] text-slate-500">
                فقط برای همین محصول تعریف می‌شود.
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <button
                type="button"
                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                @click.stop="removeCustomAttribute(i)"
                title="حذف"
            >
                <span class="text-sm leading-none">✕</span>
            </button>

            <div
                class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500">
                <span x-show="attribute._open" x-cloak class="[&_svg]:h-3 [&_svg]:w-3">
                    @include('icons.arrow-down-01')
                </span>
                <span x-show="!attribute._open" x-cloak class="[&_svg]:h-3 [&_svg]:w-3">
                    @include('icons.arrow-right-01')
                </span>
            </div>
        </div>
    </div>

    <div
        class="border-t border-slate-100 bg-white"
        x-show="attribute._open"
        x-cloak
    >
        <input type="hidden" x-bind:name="`custom_attributes[${i}][id]`" x-model="attribute.id">
        <input type="hidden" x-bind:name="`custom_attributes[${i}][sort_order]`" x-bind:value="i">

        <div class="px-4 py-3">
            <x-admin.field label="عنوان ویژگی اختصاصی">
                <x-admin.form.input
                    x-bind:name="`custom_attributes[${i}][title]`"
                    x-model="attribute.title"
                    placeholder="مثلا نسخه مود"
                />
            </x-admin.field>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-700">
                    مقادیر ویژگی
                </div>

                <button
                    type="button"
                    class="inline-flex h-8 cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-orange-200 hover:text-orange-600"
                    @click="addCustomAttributeValue(attribute)"
                >
                    <span class="text-sm leading-none">+</span>
                    <span>افزودن مقدار</span>
                </button>
            </div>

            <template x-if="attribute.values.length === 0">
                <div
                    class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                    هنوز مقداری اضافه نشده است.
                </div>
            </template>

            <div class="space-y-2">
                <template x-for="(value, valueIndex) in attribute.values" :key="value.__key">
                    <div
                        class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2 md:flex-row md:items-center">
                        <div class="min-w-0 flex-1">
                            <x-admin.form.input
                                x-bind:name="`custom_attributes[${i}][values][${valueIndex}][title]`"
                                x-model="value.title"
                                placeholder="مثلا Full Version"
                            />

                            <input
                                type="hidden"
                                x-bind:name="`custom_attributes[${i}][values][${valueIndex}][id]`"
                                x-model="value.id"
                            >

                            <input
                                type="hidden"
                                x-bind:name="`custom_attributes[${i}][values][${valueIndex}][sort_order]`"
                                x-bind:value="valueIndex"
                            >
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 hover:text-rose-700 md:w-20"
                            @click="removeCustomAttributeValue(attribute, valueIndex)"
                        >
                            حذف
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                    <input
                        type="hidden"
                        x-bind:name="`custom_attributes[${i}][visible]`"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        value="1"
                        class="cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                        x-bind:name="`custom_attributes[${i}][visible]`"
                        x-model="attribute.visible"
                    >

                    <span>نمایش در صفحه محصول</span>
                </label>

                <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                    <input
                        type="hidden"
                        x-bind:name="`custom_attributes[${i}][use_for_sale_items]`"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        value="1"
                        class="cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                        x-bind:name="`custom_attributes[${i}][use_for_sale_items]`"
                        x-model="attribute.use_for_sale_items"
                    >

                    <span>استفاده برای آیتم فروش</span>
                </label>
            </div>
        </div>
    </div>
</div>
