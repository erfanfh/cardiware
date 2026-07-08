<x-admin.form method="get" action="{{ route('admin.coupons.index') }}" class="space-y-5">
    <x-admin.filters.panel
        :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی کدهای تخفیف"
        >
            <x-slot:actions>
                <x-admin.form.button
                    tag="a"
                    href="{{ route('admin.coupons.create') }}"
                    variant="solid"
                >
                    + افزودن کدتخفیف جدید
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

                <x-admin.filters.item label="کد تخفیف">
                    <x-admin.form.input
                        name="code"
                        :value="request('code')"
                        dir="ltr"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نوع تخفیف">
                    <x-admin.form.select
                        name="type"
                        :options="['' => 'همه نوع‌ها'] + $types"
                        :value="request('type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="محدوده اعمال">
                    <x-admin.form.select
                        name="scope"
                        :options="['' => 'همه محدوده‌ها'] + $scopes"
                        :value="request('scope')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="کاربر">
                    <x-admin.form.input
                        name="user_id"
                        :value="request('user_id')"
                        placeholder="شناسه کاربر"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="محصول">
                    <x-admin.form.input
                        name="product_id"
                        :value="request('product_id')"
                        placeholder="شناسه محصول"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="فروشگاه">
                    <x-admin.form.input
                        name="store_id"
                        :value="request('store_id')"
                        placeholder="شناسه فروشگاه"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ شروع">
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

                <x-admin.filters.item label="تاریخ پایان">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="expires_at_from"
                            :value="request('expires_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="expires_at_to"
                            :value="request('expires_at_to')"
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
                <x-admin.form.button type="submit" variant="solid">
                    جستجو
                </x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.coupons.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
