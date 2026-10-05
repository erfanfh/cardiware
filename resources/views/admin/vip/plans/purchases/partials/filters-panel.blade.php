<x-admin.form method="get" action="{{ route('admin.vip-plans.purchases.index', $plan) }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="جستجو در خریدهای این پلن"/>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="کاربر">
                    <x-admin.form.input name="user" :value="request('user')" placeholder="نام، موبایل، ایمیل یا شناسه"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="شماره سفارش">
                    <x-admin.form.input name="order_number" :value="request('order_number')" dir="ltr"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت بازه">
                    <x-admin.form.select name="status" :options="['' => 'همه وضعیت‌ها', 'active' => 'فعال', 'scheduled' => 'در انتظار شروع', 'expired' => 'پایان‌یافته', 'revoked' => 'لغوشده']" :value="request('status')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ شروع">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="starts_at_from" :value="request('starts_at_from')" placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="starts_at_to" :value="request('starts_at_to')" placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ پایان">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="expires_at_from" :value="request('expires_at_from')" placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="expires_at_to" :value="request('expires_at_to')" placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ خرید">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="created_at_from" :value="request('created_at_from')" placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="created_at_to" :value="request('created_at_to')" placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.vip-plans.purchases.index', $plan) }}" variant="secondary">حذف فیلترها</x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
