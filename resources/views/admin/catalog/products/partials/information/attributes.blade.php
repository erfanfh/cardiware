<section
    x-show="activeTab === 'attributes'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-white p-5"
>
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">ویژگی‌های محصول</h3>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                ویژگی‌های عمومی را از لیست سیستم انتخاب کنید یا برای همین محصول، ویژگی اختصاصی بسازید.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-admin.form.button
                type="button"
                variant="soft"
                size="sm"
                @click="addCustomAttribute()"
            >
                <span class="[&_svg]:w-3 [&_svg]:h-3">
                    @include('icons.plus-sign')
                </span>
                <span>افزودن ویژگی اختصاصی</span>
            </x-admin.form.button>
        </div>
    </div>

    {{-- Add existing attribute --}}
    <div class="mb-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-4">
        <div class="grid grid-cols-12 gap-4 items-end">
            <div class="col-span-12 lg:col-span-8">
                <x-admin.field label="انتخاب از ویژگی‌های موجود">
                    <select
                        x-model="selectedAttributeId"
                        class="w-full h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300"
                    >
                        <option value="">یک ویژگی را انتخاب کنید</option>

                        <template x-for="attribute in availableAttributes" :key="attribute.id">
                            <option
                                :value="attribute.id"
                                x-text="attribute.title"
                                :disabled="isGlobalAttributeAssigned(attribute.id)"
                            ></option>
                        </template>
                    </select>
                </x-admin.field>
            </div>

            <div class="col-span-12 lg:col-span-4">
                <x-admin.form.button
                    type="button"
                    variant="secondary"
                    class="w-full justify-center"
                    @click="addSelectedGlobalAttribute()"
                    x-bind:disabled="!selectedAttributeId"
                >
                    <span class="[&_svg]:w-3 [&_svg]:h-3">
                        @include('icons.plus-sign')
                    </span>
                    <span>افزودن به محصول</span>
                </x-admin.form.button>
            </div>
        </div>

        <div
            class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700"
            x-show="availableAttributes.length === 0"
            x-cloak
        >
            هنوز ویژگی عمومی‌ای برای انتخاب وجود ندارد.
        </div>
    </div>

    {{-- Empty state --}}
    <template x-if="assignedAttributes.length === 0 && customAttributes.length === 0">
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-8 text-center">
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                <span class="text-xl leading-none">✦</span>
            </div>

            <div class="mt-3 text-sm font-semibold text-slate-700">
                هنوز ویژگی‌ای برای این محصول اضافه نشده است.
            </div>

            <div class="mx-auto mt-1 max-w-xl text-xs leading-6 text-slate-500">
                می‌توانید از ویژگی‌های عمومی سیستم انتخاب کنید یا یک ویژگی اختصاصی برای همین محصول بسازید.
            </div>
        </div>
    </template>

    <div class="space-y-4">
        {{-- Global attributes --}}
        <template x-for="(attribute, i) in assignedAttributes" :key="attribute.__key">
            <div class="rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300">
                <div class="flex flex-wrap items-start justify-between gap-3 px-4 py-3.5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="text-sm font-bold text-slate-800" x-text="attribute.title"></div>

                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-blue-700">
                                ویژگی عمومی
                            </span>
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            مقادیر مجاز این ویژگی از بخش مدیریت ویژگی‌ها خوانده می‌شود.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                        @click="removeGlobalAttribute(i)"
                        title="حذف"
                    >
                        <span class="text-sm leading-none">✕</span>
                    </button>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-4">
                    <input
                        type="hidden"
                        x-bind:name="`attributes[${i}][id]`"
                        x-model="attribute.id"
                    >

                    <input
                        type="hidden"
                        x-bind:name="`attributes[${i}][attribute_id]`"
                        x-model="attribute.attribute_id"
                    >

                    <input
                        type="hidden"
                        x-bind:name="`attributes[${i}][sort_order]`"
                        x-bind:value="i"
                    >

                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 text-xs font-semibold text-slate-700">
                                مقادیر ویژگی
                            </div>

                            <template x-if="attribute.values.length === 0">
                                <div
                                    class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">
                                    برای این ویژگی هنوز مقداری تعریف نشده است.
                                </div>
                            </template>

                            <div class="flex flex-wrap gap-2">
                                <template x-for="value in attribute.values" :key="value.id">
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 transition hover:border-orange-300 hover:text-orange-600">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                            x-bind:name="`attributes[${i}][value_ids][]`"
                                            x-bind:value="value.id"
                                            x-bind:checked="attribute.selected_value_ids.includes(value.id)"
                                            @change="toggleGlobalAttributeValue(attribute, value.id)"
                                        >

                                        <span x-text="value.title"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <label
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        نمایش در صفحه محصول
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        این ویژگی در مشخصات محصول نمایش داده شود.
                                    </div>
                                </div>

                                <div>
                                    <input
                                        type="hidden"
                                        x-bind:name="`attributes[${i}][visible]`"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        value="1"
                                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                        x-bind:name="`attributes[${i}][visible]`"
                                        x-model="attribute.visible"
                                    >
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        استفاده برای آیتم فروش
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        برای ساخت ترکیب‌های محصول متغیر استفاده شود.
                                    </div>
                                </div>

                                <div>
                                    <input
                                        type="hidden"
                                        x-bind:name="`attributes[${i}][use_for_sale_items]`"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        value="1"
                                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                        x-bind:name="`attributes[${i}][use_for_sale_items]`"
                                        x-model="attribute.use_for_sale_items"
                                    >
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Custom attributes --}}
        <template x-for="(attribute, i) in customAttributes" :key="attribute.__key">
            <div class="rounded-2xl border border-slate-200 bg-white transition hover:border-slate-300">
                <div class="flex flex-wrap items-start justify-between gap-3 px-4 py-3.5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <div
                                class="text-sm font-bold text-slate-800"
                                x-text="attribute.title && String(attribute.title).trim().length ? attribute.title : 'ویژگی اختصاصی جدید'"
                                :class="!attribute.title || !String(attribute.title).trim().length ? 'text-slate-400 font-medium' : ''"
                            ></div>

                            <span
                                class="rounded-full bg-purple-50 px-2.5 py-1 text-[11px] font-medium text-purple-700">
                                ویژگی اختصاصی
                            </span>
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            این ویژگی فقط برای همین محصول استفاده می‌شود.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                        @click="removeCustomAttribute(i)"
                        title="حذف"
                    >
                        <span class="text-sm leading-none">✕</span>
                    </button>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-4">
                    <input
                        type="hidden"
                        x-bind:name="`custom_attributes[${i}][id]`"
                        x-model="attribute.id"
                    >

                    <input
                        type="hidden"
                        x-bind:name="`custom_attributes[${i}][sort_order]`"
                        x-bind:value="i"
                    >

                    <div class="space-y-4">
                        <x-admin.field label="عنوان ویژگی اختصاصی">
                            <x-admin.form.input
                                x-bind:name="`custom_attributes[${i}][title]`"
                                x-model="attribute.title"
                                placeholder="مثلا نسخه مود"
                            />
                        </x-admin.field>

                        <div>
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-semibold text-slate-700">
                                        مقادیر ویژگی اختصاصی
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        مقدارها از پیش تعریف می‌شوند و ادمین باید از همین مقادیر استفاده کند.
                                    </div>
                                </div>

                                <x-admin.form.button
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    @click="addCustomAttributeValue(attribute)"
                                >
                                    <span class="[&_svg]:w-3 [&_svg]:h-3">
                                        @include('icons.plus-sign')
                                    </span>
                                    <span>افزودن مقدار</span>
                                </x-admin.form.button>
                            </div>

                            <template x-if="attribute.values.length === 0">
                                <div
                                    class="rounded-xl border border-dashed border-slate-300 bg-white px-3 py-4 text-xs text-slate-500">
                                    هنوز مقداری برای این ویژگی اختصاصی اضافه نشده است.
                                </div>
                            </template>

                            <div class="space-y-2">
                                <template x-for="(value, valueIndex) in attribute.values" :key="value.__key">
                                    <div
                                        class="grid grid-cols-12 gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                        <div class="col-span-12 md:col-span-10">
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

                                        <div class="col-span-12 md:col-span-2">
                                            <button
                                                type="button"
                                                class="flex h-10 w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                                                @click="removeCustomAttributeValue(attribute, valueIndex)"
                                            >
                                                حذف
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <label
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        نمایش در صفحه محصول
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        این ویژگی در مشخصات محصول نمایش داده شود.
                                    </div>
                                </div>

                                <div>
                                    <input
                                        type="hidden"
                                        x-bind:name="`custom_attributes[${i}][visible]`"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        value="1"
                                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                        x-bind:name="`custom_attributes[${i}][visible]`"
                                        x-model="attribute.visible"
                                    >
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-700">
                                        استفاده برای آیتم فروش
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        در ساخت آیتم‌های فروش محصول متغیر استفاده شود.
                                    </div>
                                </div>

                                <div>
                                    <input
                                        type="hidden"
                                        x-bind:name="`custom_attributes[${i}][use_for_sale_items]`"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        value="1"
                                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                        x-bind:name="`custom_attributes[${i}][use_for_sale_items]`"
                                        x-model="attribute.use_for_sale_items"
                                    >
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</section>
