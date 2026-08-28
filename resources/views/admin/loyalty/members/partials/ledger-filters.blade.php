<x-admin.form method="get" action="{{ route('admin.loyalty-members.show', $user) }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="فیلتر تاریخچه امتیاز"/>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="نوع تراکنش">
                    <x-admin.form.select
                        name="type"
                        :options="['' => 'همه انواع'] + $types"
                        :value="request('type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="جهت تراکنش">
                    <x-admin.form.select
                        name="entry_direction"
                        :options="['' => 'همه جهت‌ها'] + $directions"
                        :value="request('entry_direction')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="علت سیستمی">
                    <x-admin.form.input name="reason" :value="request('reason')" placeholder="purchase, comment, ..." dir="ltr"/>
                </x-admin.filters.item>

                <x-admin.filters.item label="مقدار امتیاز">
                    <div class="flex gap-2">
                        <x-admin.form.input name="amount_from" type="number" min="1" :value="request('amount_from')" placeholder="از"/>
                        <x-admin.form.input name="amount_to" type="number" min="1" :value="request('amount_to')" placeholder="تا"/>
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="زمان رخداد">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="occurred_at_from" :value="request('occurred_at_from')" placeholder="از تاریخ"/>
                        <x-admin.form.date-picker name="occurred_at_to" :value="request('occurred_at_to')" placeholder="تا تاریخ"/>
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">اعمال فیلتر</x-admin.form.button>
                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.loyalty-members.show', $user) }}" variant="secondary">
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
