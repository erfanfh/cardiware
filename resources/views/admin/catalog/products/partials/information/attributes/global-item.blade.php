<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300">
    <div
        class="flex cursor-pointer items-center justify-between gap-3 px-4 py-3"
        @click="attribute._open = !attribute._open"
    >
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <div class="truncate text-sm font-extrabold text-slate-900" x-text="attribute.title"></div>

                <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">
                    عمومی
                </span>

                <span
                    class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"
                    x-text="attribute.selected_value_ids.length + ' مقدار'"
                ></span>
            </div>

            <div class="mt-1 text-[11px] text-slate-500">
                از ویژگی‌های عمومی سیستم انتخاب شده است.
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <button
                type="button"
                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                @click.stop="removeGlobalAttribute(i)"
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
        <input type="hidden" x-bind:name="`attributes[${i}][id]`" x-model="attribute.id">
        <input type="hidden" x-bind:name="`attributes[${i}][attribute_id]`" x-model="attribute.attribute_id">
        <input type="hidden" x-bind:name="`attributes[${i}][sort_order]`" x-bind:value="i">

        <div class="px-4 py-3">
            <div class="mb-2 text-xs font-bold text-slate-700">
                مقادیر ویژگی
            </div>

            <template x-if="attribute.values.length === 0">
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">
                    برای این ویژگی هنوز مقداری تعریف نشده است.
                </div>
            </template>

            <div class="flex flex-wrap gap-2">
                <template x-for="value in attribute.values" :key="value.id">
                    <label
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-xs font-medium transition"
                        :class="attribute.selected_value_ids.includes(Number(value.id))
                            ? 'border-orange-200 bg-orange-50 text-orange-700'
                            : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-orange-300 hover:bg-white hover:text-orange-600'"
                    >
                        <input
                            type="checkbox"
                            class="cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                            x-bind:name="`attributes[${i}][value_ids][]`"
                            x-bind:value="value.id"
                            x-bind:checked="attribute.selected_value_ids.includes(Number(value.id))"
                            @change="toggleGlobalAttributeValue(attribute, value.id)"
                        >

                        <span x-text="value.title"></span>
                    </label>
                </template>
            </div>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                    <input
                        type="hidden"
                        x-bind:name="`attributes[${i}][is_visible]`"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        value="1"
                        class="cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                        x-bind:name="`attributes[${i}][is_visible]`"
                        x-model="attribute.is_visible"
                    >

                    <span>نمایش در صفحه محصول</span>
                </label>

                <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-700">
                    <input
                        type="hidden"
                        x-bind:name="`attributes[${i}][is_variation_defining]`"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        value="1"
                        class="cursor-pointer rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                        x-bind:name="`attributes[${i}][is_variation_defining]`"
                        x-model="attribute.is_variation_defining"
                    >

                    <span>استفاده برای آیتم فروش</span>
                </label>
            </div>
        </div>
    </div>
</div>
