<section
    x-show="activeTab === 'attributes'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-slate-900">ویژگی‌های محصول</h3>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                ویژگی‌های عمومی یا اختصاصی محصول را مدیریت کنید.
            </p>
        </div>
    </div>

    @include('admin.catalog.products.partials.information.attributes.add-existing')

    @include('admin.catalog.products.partials.information.attributes.empty-state')

    <div class="space-y-3">
        <template x-for="(attribute, i) in assignedAttributes" :key="attribute.__key">
            @include('admin.catalog.products.partials.information.attributes.global-item')
        </template>

        <template x-for="(attribute, i) in customAttributes" :key="attribute.__key">
            @include('admin.catalog.products.partials.information.attributes.custom-item')
        </template>
    </div>
</section>
