<section
    x-show="activeTab === 'addons'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
>
    <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">خدمات افزوده محصول</h3>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                گزینه‌هایی مثل نصب حضوری، پشتیبانی بیشتر، راه‌اندازی یا خدمات همراه محصول را مدیریت کنید.
            </p>
        </div>
    </div>

    <x-admin.form.repeater
        name="addons"
        title="لیست خدمات افزوده"
        add-text="افزودن خدمت افزوده"
        empty-text="هنوز گزینه‌ای برای خرید این محصول تعریف نشده است."
        heading-key="title"
        heading-placeholder="خدمت بدون عنوان"
        :items="$productAddons"
        :default-item="[
            'id' => null,
            'title' => '',
            'description' => '',
            'price' => 0,
            'is_active' => true,
            'sort_order' => 0,
            '_open' => true,
        ]"
    >
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <input
            type="hidden"
            x-bind:name="field(i, 'sort_order')"
            x-model="it.sort_order"
        >

        <div class="space-y-5">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 md:col-span-6">
                    <x-admin.field label="عنوان">
                        <x-admin.form.input
                            x-bind:name="field(i, 'title')"
                            x-model="it.title"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-3">
                    <x-admin.field label="قیمت (تومان)">
                        <x-admin.form.input
                            type="number"
                            min="0"
                            x-bind:name="field(i, 'price')"
                            x-model="it.price"
                            class="direction-ltr"
                            placeholder="0"
                        />
                    </x-admin.field>
                </div>

                <div class="col-span-12 md:col-span-3">
                    <x-admin.field label="وضعیت">
                        <x-admin.form.select
                            x-model="it.is_active"
                            x-bind:name="field(i, 'is_active')"
                            :options="[
                                ['value' => true, 'label' => 'فعال'],
                                ['value' => false, 'label' => 'غیرفعال'],
                            ]"
                        />
                    </x-admin.field>
                </div>
            </div>

            <div>
                <x-admin.field label="توضیحات">
                <textarea
                    x-bind:name="field(i, 'description')"
                    x-model="it.description"
                    rows="3"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-7 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10"
                    placeholder="توضیح کوتاهی درباره این خدمت افزوده بنویسید."
                ></textarea>
                </x-admin.field>
            </div>
        </div>
    </x-admin.form.repeater>
</section>
