<x-admin.form
    method="get"
    action="{{ route('admin.coupons.redemptions.index', $coupon) }}"
    class="space-y-5"
>
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="جستجو در تاریخچه استفاده"/>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="کاربر">
                    <x-admin.form.input
                        name="user"
                        :value="request('user')"
                        placeholder="نام، موبایل، ایمیل یا شناسه"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="شماره سفارش">
                    <x-admin.form.input
                        name="order_number"
                        :value="request('order_number')"
                        dir="ltr"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="مبلغ تخفیف">
                    <div class="flex gap-2">
                        <x-admin.form.input
                            name="discount_from"
                            type="number"
                            min="0"
                            :value="request('discount_from')"
                            placeholder="از مبلغ"
                        />

                        <x-admin.form.input
                            name="discount_to"
                            type="number"
                            min="0"
                            :value="request('discount_to')"
                            placeholder="تا مبلغ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ استفاده">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="redeemed_at_from"
                            :value="request('redeemed_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="redeemed_at_to"
                            :value="request('redeemed_at_to')"
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
                        href="{{ route('admin.coupons.redemptions.index', $coupon) }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
