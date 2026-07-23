<x-admin.form
    method="get"
    action="{{ route('admin.team-members.index') }}"
    class="space-y-5"
>
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی اعضای تیم"
        >
            <x-slot:actions>
                <x-admin.form.button
                    tag="a"
                    href="{{ route('admin.team-members.create') }}"
                    variant="solid"
                >
                    + افزودن عضو جدید
                </x-admin.form.button>
            </x-slot:actions>
        </x-admin.filters.header>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input
                        name="id"
                        type="number"
                        min="1"
                        :value="request('id')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نام">
                    <x-admin.form.input
                        name="first_name"
                        :value="request('first_name')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نام خانوادگی">
                    <x-admin.form.input
                        name="last_name"
                        :value="request('last_name')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="عنوان شغلی">
                    <x-admin.form.input
                        name="job_title"
                        :value="request('job_title')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="موبایل">
                    <x-admin.form.input
                        name="mobile"
                        dir="ltr"
                        :value="request('mobile')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="ایمیل">
                    <x-admin.form.input
                        name="email"
                        dir="ltr"
                        :value="request('email')"
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
                <x-admin.form.button
                    type="submit"
                    variant="solid"
                >
                    جستجو
                </x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.team-members.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
