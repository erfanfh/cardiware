<section
    x-show="activeTab === 'basic'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">اطلاعات اصلی محصول</h3>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                نوع، ساختار و شناسه محصول را مشخص کنید.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
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

        <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <x-admin.field label="نوع محتوا">
                <x-admin.form.select
                    name="content_type"
                    x-model="productContentType"
                    :value="$currentContentType"
                    :options="[
                    '' => 'انتخاب کنید',
                    ...$productContentTypes
                    ]"
                />
            </x-admin.field>
        </div>

        <div class="col-span-12 md:col-span-6 xl:col-span-3">
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

        <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <x-admin.field label="شناسه محصول">
                <x-admin.form.input
                    name="sku"
                    :value="$currentSku"
                    class="direction-ltr"
                />
            </x-admin.field>
        </div>
    </div>

    <div class="mt-5 border-t border-slate-200 pt-4">
        <p class="text-xs leading-6 text-slate-500">
            انتخاب ساختار محصول روی بخش قیمت‌گذاری، فایل‌ها و آیتم‌های فروش تاثیر می‌گذارد.
        </p>
    </div>
</section>
