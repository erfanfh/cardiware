<x-admin.form method="get" action="{{ route('admin.abandoned-carts.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="جستجو و فیلتر سبدهای خرید"/>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="جستجو">
                    <x-admin.form.input
                        name="search"
                        :value="request('search')"
                        placeholder="شناسه سبد، نام، موبایل، ایمیل یا Session"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نوع کاربر">
                    <x-admin.form.select
                        name="customer_type"
                        :options="[
                            '' => 'همه کاربران',
                            'user' => 'کاربر عضو',
                            'guest' => 'کاربر مهمان',
                        ]"
                        :value="request('customer_type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="مبلغ سبد">
                    <div class="flex gap-2">
                        <x-admin.form.input
                            type="number"
                            name="total_from"
                            :value="request('total_from')"
                            placeholder="از"
                            min="0"
                        />
                        <x-admin.form.input
                            type="number"
                            name="total_to"
                            :value="request('total_to')"
                            placeholder="تا"
                            min="0"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="آخرین تغییر">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="updated_from"
                            :value="request('updated_from')"
                            placeholder="از تاریخ"
                        />
                        <x-admin.form.date-picker
                            name="updated_to"
                            :value="request('updated_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="مرتب سازی">
                    <div class="flex gap-2">
                        <x-admin.form.select
                            name="sort"
                            :options="[
                                'updated_at' => 'آخرین تغییر',
                                'created_at' => 'زمان ایجاد',
                                'total_amount' => 'مبلغ سبد',
                            ]"
                            :value="request('sort', 'updated_at')"
                        />
                        <x-admin.form.select
                            name="direction"
                            :options="[
                                'desc' => 'نزولی',
                                'asc' => 'صعودی',
                            ]"
                            :value="request('direction', 'desc')"
                        />
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">اعمال فیلتر</x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.abandoned-carts.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
