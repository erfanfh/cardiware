<x-admin.form method="get" action="{{ route('admin.account-announcements.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی اطلاعیه‌های حساب کاربری">
            <x-slot:actions>
                <x-admin.form.button tag="a" href="{{ route('admin.account-announcements.create') }}" variant="solid">
                    + افزودن اطلاعیه جدید
                </x-admin.form.button>
            </x-slot:actions>
        </x-admin.filters.header>
        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="عنوان">
                    <x-admin.form.input name="title" :value="request('title')" maxlength="180"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="وضعیت فعال‌سازی">
                    <x-admin.form.select name="is_active" :value="request('is_active', '')" :options="['' => 'همه', '1' => 'فعال', '0' => 'غیرفعال']"/>
                </x-admin.filters.item>
            </x-admin.filters.grid>
            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.account-announcements.index') }}" variant="secondary">حذف فیلترها</x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
