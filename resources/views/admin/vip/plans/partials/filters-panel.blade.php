<x-admin.form method="get" action="{{ route('admin.vip-plans.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی پلن‌های VIP">
            <x-slot:actions>
                @can('vip-plan.create')
                    <x-admin.form.button tag="a" href="{{ route('admin.vip-plans.create') }}" variant="solid">
                        + افزودن پلن جدید
                    </x-admin.form.button>
                @endcan
            </x-slot:actions>
        </x-admin.filters.header>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input name="id" type="number" min="1" :value="request('id')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="عنوان">
                    <x-admin.form.input name="title" :value="request('title')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="کد پلن">
                    <x-admin.form.input name="code" dir="ltr" :value="request('code')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select name="is_active"
                                         :options="['' => 'همه وضعیت‌ها', '1' => 'فعال', '0' => 'غیرفعال']"
                                         :value="request('is_active')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="مدت اعتبار (روز)">
                    <div class="flex gap-2">
                        <x-admin.form.input name="duration_days_min" type="number" min="1"
                                            placeholder="از" :value="request('duration_days_min')"/>
                        <x-admin.form.input name="duration_days_max" type="number" min="1"
                                            placeholder="تا" :value="request('duration_days_max')"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="قیمت (تومان)">
                    <div class="flex gap-2">
                        <x-admin.form.input name="price_toman_min" type="number" min="0"
                                            placeholder="از" :value="request('price_toman_min')"/>
                        <x-admin.form.input name="price_toman_max" type="number" min="0"
                                            placeholder="تا" :value="request('price_toman_max')"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ ایجاد">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="created_at_from" :value="request('created_at_from')"
                                                  placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="created_at_to" :value="request('created_at_to')"
                                                  placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.vip-plans.index') }}" variant="secondary">
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
