<x-admin.form method="get" action="{{ route('admin.roles.index') }}" class="space-y-5">
    <x-admin.filters.panel
        :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی نقش‌ها"
        >
            <x-slot:actions>
                <x-admin.form.button
                    tag="a"
                    href="{{ route('admin.roles.create') }}"
                    variant="solid"
                >
                    + افزودن نقش جدید
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

                <x-admin.filters.item label="دسترسی‌ها">
                    <x-admin.form.async-select
                        name="permission_ids"
                        :value="request('permission_ids')"
                        :selected-options="$selectedPermissionOptions"
                        placeholder="جستجو در دسترسی‌ها..."
                        :multiple="true"
                        :search-url="route('admin.permissions.ajax-search')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ ایجاد">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="created_at_from"
                            :value="request('created_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="created_at_to"
                            :value="request('created_at_to')"
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
                        href="{{ route('admin.roles.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
