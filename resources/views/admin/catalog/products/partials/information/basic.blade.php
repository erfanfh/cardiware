<section
    x-show="activeTab === 'basic'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-white p-5"
>
    <div class="mb-5">
        <h3 class="text-sm font-bold text-slate-800">اطلاعات اصلی محصول</h3>
        <p class="mt-1 text-xs leading-6 text-slate-500">
            نوع و ساختار محصول مشخص می‌کند بخش قیمت‌گذاری، فایل‌ها و آیتم‌های فروش چگونه نمایش داده شوند.
        </p>
    </div>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12 md:col-span-6 xl:col-span-4">
            <x-admin.field label="نوع محصول">
                <x-admin.form.select
                    name="type"
                    x-model="productType"
                    :value="$currentType"
                    :options="[
                            $digitalType => 'مجازی',
                            $physicalType => 'فیزیکی',
                        ]"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 md:col-span-6 xl:col-span-4">
            <x-admin.field label="ساختار محصول">
                <x-admin.form.select
                    name="structure"
                    x-model="productStructure"
                    :value="$currentStructure"
                    :options="[
                            $simpleStructure => 'ساده',
                            $variableStructure => 'متغیر',
                        ]"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 xl:col-span-4">
            <x-admin.field label="شناسه محصول">
                <x-admin.form.input
                    name="sku"
                    :value="$currentSku"
                    class="direction-ltr"
                />
            </x-admin.field>
        </div>
    </div>

    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
            <span class="font-semibold text-slate-700">وضعیت فعلی:</span>

            <span
                class="inline-flex rounded-full bg-white px-2.5 py-1 font-medium text-slate-700"
                x-text="productTypeLabel"
            ></span>

            <span class="text-slate-300">/</span>

            <span
                class="inline-flex rounded-full bg-white px-2.5 py-1 font-medium text-slate-700"
                x-text="productStructureLabel"
            ></span>
        </div>
    </div>
</section>
