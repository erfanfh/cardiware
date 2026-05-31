<section
    x-show="activeTab === 'pricing'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-white p-5"
>
    <div class="mb-5">
        <h3 class="text-sm font-bold text-slate-800">قیمت‌گذاری و فایل‌ها</h3>
        <p class="mt-1 text-xs leading-6 text-slate-500">
            محتوای این بخش بر اساس نوع محصول و ساختار محصول تغییر می‌کند.
        </p>
    </div>

    @include('admin.catalog.products.partials.information.pricing-physical')

    @include('admin.catalog.products.partials.information.pricing-digital-simple')

    @include('admin.catalog.products.partials.information.pricing-digital-variable')
</section>
