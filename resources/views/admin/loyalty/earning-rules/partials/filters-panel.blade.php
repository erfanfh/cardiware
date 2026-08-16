<x-admin.form method="get" action="{{ route('admin.earning-rules.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی قوانین کسب امتیاز">
            <x-slot:actions>
                <x-admin.form.button
                    tag="a"
                    href="{{ route('admin.earning-rules.create') }}"
                    variant="solid"
                >
                    + افزودن قانون جدید
                </x-admin.form.button>
            </x-slot:actions>
        </x-admin.filters.header>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input name="id" :value="request('id')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="عنوان">
                    <x-admin.form.input name="title" :value="request('title')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="کد قانون">
                    <x-admin.form.input name="code" dir="ltr" :value="request('code')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="نوع قانون">
                    <x-admin.form.select
                        name="type"
                        :options="['' => 'همه نوع‌ها'] + $types"
                        :value="request('type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="is_active"
                        :options="['' => 'همه وضعیت‌ها', '1' => 'فعال', '0' => 'غیرفعال']"
                        :value="request('is_active')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="اولویت">
                    <x-admin.form.input
                        name="priority"
                        type="number"
                        min="0"
                        dir="ltr"
                        :value="request('priority')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="زمان شروع">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="starts_at_from"
                            :value="request('starts_at_from')"
                            placeholder="از تاریخ"
                        />
                        <x-admin.form.date-picker
                            name="starts_at_to"
                            :value="request('starts_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="زمان پایان">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="ends_at_from"
                            :value="request('ends_at_from')"
                            placeholder="از تاریخ"
                        />
                        <x-admin.form.date-picker
                            name="ends_at_to"
                            :value="request('ends_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
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
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.earning-rules.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
