<div class="mb-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
    <div class="grid grid-cols-12 items-end gap-3">
        <div class="col-span-12 lg:col-span-5">
            <x-admin.field>
                <select
                    x-model="selectedAttributeId"
                    class="h-10 w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-200"
                >
                    <option value="">انتخاب ویژگی موجود</option>

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

        <div class="col-span-6 lg:col-span-2">
            <x-admin.form.button
                type="button"
                variant="secondary"
                size="sm"
                class="h-10 justify-center"
                @click="addSelectedGlobalAttribute()"
                x-bind:disabled="!selectedAttributeId"
            >
                <span class="[&_svg]:h-3 [&_svg]:w-3">
                    @include('icons.plus-sign')
                </span>
                <span>افزودن</span>
            </x-admin.form.button>
        </div>

        <div class="col-span-6 lg:col-span-5">
            <div class="flex justify-end">
                <x-admin.form.button
                    type="button"
                    variant="soft"
                    size="sm"
                    class="h-10 justify-center"
                    @click="addCustomAttribute()"
                >
                <span class="[&_svg]:h-3 [&_svg]:w-3">
                    @include('icons.plus-sign')
                </span>
                    <span>افزودن ویژگی اختصاصی</span>
                </x-admin.form.button>
            </div>
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
