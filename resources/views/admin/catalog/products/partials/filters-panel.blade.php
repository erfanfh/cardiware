<x-admin.form method="get" action="{{ route('admin.products.index') }}" class="space-y-5">
    <x-admin.filters.panel
        :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی محصولات"
        >
            <x-slot:actions>
                <x-admin.form.button
                    tag="a"
                    href="{{ route('admin.products.create') }}"
                    variant="solid"
                >
                    + افزودن محصول جدید
                </x-admin.form.button>
            </x-slot:actions>
        </x-admin.filters.header>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input
                        name="id"
                        :value="request('id')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="عنوان">
                    <x-admin.form.input
                        name="title"
                        :value="request('title')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="شناسه محصول">
                    <x-admin.form.input
                        name="sku"
                        :value="request('sku')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="فروشنده">
                    <x-admin.form.input
                        name="store_id"
                        :value="request('store_id')"
                        placeholder="شناسه فروشنده"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نوع محصول">
                    <x-admin.form.select
                        name="type"
                        :options="['' => 'همه نوع‌ها'] + $types"
                        :value="request('type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="ساختار محصول">
                    <x-admin.form.select
                        name="structure"
                        :options="['' => 'همه ساختارها'] + $structures"
                        :value="request('structure')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="دسته‌بندی‌ها">
                    <x-admin.form.async-select
                        name="category_ids"
                        :value="request('category_ids')"
                        :selected-options="$selectedCategoryOptions"
                        placeholder="جستجو در دسته‌بندی‌های محصول..."
                        :multiple="true"
                        :search-url="route('admin.products.categories.ajax-search')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="برچسب‌ها">
                    <x-admin.form.async-select
                        name="tag_ids"
                        :value="request('tag_ids')"
                        :selected-options="$selectedTagOptions"
                        placeholder="جستجو در برچسب‌های محصول..."
                        :multiple="true"
                        :search-url="route('admin.products.tags.ajax-search')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ انتشار">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="published_at_from"
                            :value="request('published_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="published_at_to"
                            :value="request('published_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">
                    جستجو
                </x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.products.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
