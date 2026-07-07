<x-admin.form method="get" action="{{ route('admin.tickets.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی تیکت‌ها"
        />

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شماره">
                    <x-admin.form.input
                        name="number"
                        :value="request('number')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نام کاربر">
                    <x-admin.form.input
                        name="user_name"
                        :value="request('user_name')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="موبایل کاربر">
                    <x-admin.form.input
                        name="user_mobile"
                        :value="request('user_mobile')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="ایمیل کاربر">
                    <x-admin.form.input
                        name="user_email"
                        :value="request('user_email')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="عنوان تیکت">
                    <x-admin.form.input
                        name="title"
                        :value="request('title')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="متن پیام">
                    <x-admin.form.input
                        name="message"
                        :value="request('message')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="دپارتمان">
                    <x-admin.form.select
                        name="department_id"
                        :options="['' => 'همه دپارتمان‌ها'] + $departments"
                        :value="request('department_id')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="محصول مرتبط">
                    <x-admin.form.async-select
                        name="product_id"
                        :value="request('product_id')"
                        :selected-options="$selectedProduct"
                        placeholder="جستجو در محصولات..."
                        :multiple="false"
                        :search-url="route('admin.products.ajax-search')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ ثبت">
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

                <x-admin.filters.item label="آخرین پیام">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="last_message_at_from"
                            :value="request('last_message_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="last_message_at_to"
                            :value="request('last_message_at_to')"
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
                        href="{{ route('admin.tickets.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
