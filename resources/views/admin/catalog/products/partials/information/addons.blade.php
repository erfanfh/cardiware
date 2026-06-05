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
            'max_quantity_per_order' => 1,
            'requires_purchase' => true,
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
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12 md:col-span-3">
                <x-admin.field label="حداکثر تعداد در هر سفارش">
                    <x-admin.form.input
                        type="number"
                        min="1"
                        max="1"
                        step="1"
                        x-bind:name="field(i, 'max_quantity_per_order')"
                        x-model="it.max_quantity_per_order"
                        class="direction-ltr"
                    />
                </x-admin.field>
            </div>

            <div class="col-span-12">
                <x-admin.field label="توضیحات">
                    <textarea
                        x-bind:name="field(i, 'description')"
                        x-model="it.description"
                        rows="3"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10"
                        placeholder="توضیح کوتاهی درباره این گزینه خرید بنویسید."
                    ></textarea>
                </x-admin.field>
            </div>

            <div class="col-span-12">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300"
                        >
                            <input
                                type="hidden"
                                x-bind:name="field(i, 'requires_purchase')"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                value="1"
                                x-bind:name="field(i, 'requires_purchase')"
                                x-model="it.requires_purchase"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            >

                            <span>
                                <span class="block text-sm font-bold text-slate-800">
                                    فقط همراه با خرید محصول قابل تهیه باشد
                                </span>

                                <span class="mt-1 block text-xs leading-6 text-slate-500">
                                    اگر فعال باشد، این گزینه به‌صورت مستقل قابل خرید نیست و فقط کنار محصول اصلی استفاده می‌شود.
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="col-span-12 md:col-span-6">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300"
                        >
                            <input
                                type="hidden"
                                x-bind:name="field(i, 'is_active')"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                value="1"
                                x-bind:name="field(i, 'is_active')"
                                x-model="it.is_active"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            >

                            <span>
                                <span class="block text-sm font-bold text-slate-800">
                                    فعال باشد
                                </span>

                                <span class="mt-1 block text-xs leading-6 text-slate-500">
                                    اگر غیرفعال شود، این گزینه برای کاربر قابل انتخاب نخواهد بود.
                                </span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.form.repeater>
</section>
