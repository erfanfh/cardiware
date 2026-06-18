<x-admin.form method="get" action="{{ route('admin.orders.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">

        <x-admin.filters.header title="مدیریت و جستجوی سفارش‌ها"/>

        <x-admin.filters.body>

            <x-admin.filters.grid>

                <x-admin.filters.item label="شماره سفارش">
                    <x-admin.form.input name="order_number" :value="request('order_number')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="کاربر">
                    <x-admin.form.input name="user" :value="request('user')" placeholder="شناسه یا ایمیل یا ..."/>
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="مبلغ">
                    <div class="flex gap-2">
                        <x-admin.form.input type="number" name="total_from" :value="request('total_from')"
                                            placeholder="از"/>
                        <x-admin.form.input type="number" name="total_to" :value="request('total_to')"
                                            placeholder="تا"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ سفارش">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="date_from" :value="request('date_from')"
                                                  placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="date_to" :value="request('date_to')" placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>

            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.orders.index') }}" variant="secondary">
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>

        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
