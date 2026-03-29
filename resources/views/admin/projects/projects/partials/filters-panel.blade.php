<x-admin.form method="get" action="{{ route('admin.projects.index') }}" class="space-y-5">
    <x-admin.filters.panel
        :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی پروژه‌ها"
        >
            <x-slot:actions>
                <x-admin.form.button
                    tag="a"
                    href="{{ route('admin.projects.create') }}"
                    variant="solid"
                >
                    + افزودن پروژه جدید
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

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="درصد پیشرفت">
                    <div class="flex gap-2">
                        <x-admin.form.input
                            type="number"
                            name="progress_percent_from"
                            min="0"
                            max="100"
                            :value="request('progress_percent_from')"
                            placeholder="از"
                        />

                        <x-admin.form.input
                            type="number"
                            name="progress_percent_to"
                            min="0"
                            max="100"
                            :value="request('progress_percent_to')"
                            placeholder="تا"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ انتشار پروژه">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="released_at_from"
                            :value="request('released_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="released_at_to"
                            :value="request('released_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ آخرین بروزرسانی پروژه">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="last_updated_at_from"
                            :value="request('last_updated_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="last_updated_at_to"
                            :value="request('last_updated_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="برچسب‌ها">
                    <x-admin.form.async-select
                        name="tag_ids"
                        :value="request('tag_ids')"
                        :selected-options="$selectedTagOptions"
                        placeholder="جستجو در برچسب‌ها..."
                        :multiple="true"
                        :search-url="route('admin.tags.ajax-search')"
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
                        href="{{ route('admin.projects.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
