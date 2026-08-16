<x-admin.form method="get" action="{{ route('admin.loyalty-members.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی کاربران باشگاه مخاطبان"/>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه کاربر">
                    <x-admin.form.input name="id" :value="request('id')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="جستجوی کاربر">
                    <x-admin.form.input
                        name="query"
                        :value="request('query')"
                        placeholder="نام، نام کاربری، موبایل یا ایمیل"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="موبایل">
                    <x-admin.form.input name="mobile" :value="request('mobile')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="ایمیل">
                    <x-admin.form.input name="email" :value="request('email')"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="نقش">
                    <x-admin.form.select
                        name="role"
                        :options="['' => 'همه نقش‌ها'] + $roles"
                        :value="request('role')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت کاربر">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت حساب امتیاز">
                    <x-admin.form.select
                        name="has_account"
                        :options="['' => 'همه کاربران', '1' => 'حساب امتیاز دارد', '0' => 'هنوز حساب امتیاز ندارد']"
                        :value="request('has_account')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="موجودی فعلی">
                    <div class="flex gap-2">
                        <x-admin.form.input name="balance_from" type="number" :value="request('balance_from')" placeholder="از"/>
                        <x-admin.form.input name="balance_to" type="number" :value="request('balance_to')" placeholder="تا"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="کل امتیاز کسب شده">
                    <div class="flex gap-2">
                        <x-admin.form.input name="lifetime_earned_from" type="number" min="0" :value="request('lifetime_earned_from')" placeholder="از"/>
                        <x-admin.form.input name="lifetime_earned_to" type="number" min="0" :value="request('lifetime_earned_to')" placeholder="تا"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ عضویت">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="created_at_from" :value="request('created_at_from')" placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="created_at_to" :value="request('created_at_to')" placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.loyalty-members.index') }}" variant="secondary">
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
