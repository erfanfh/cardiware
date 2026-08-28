<x-admin.form method="get" action="{{ route('admin.draw-campaigns.tickets.index',$drawCampaign) }}">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="فیلتر بلیط ها"/>
        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="کد بلیط"><x-admin.form.input name="code" dir="ltr" :value="request('code')"/></x-admin.filters.item>
                <x-admin.filters.item label="وضعیت"><x-admin.form.select name="status" :options="[''=>'همه وضعیت ها']+$statuses" :value="request('status')"/></x-admin.filters.item>
                <x-admin.filters.item label="شناسه کاربر"><x-admin.form.input name="user_id" type="number" :value="request('user_id')"/></x-admin.filters.item>
                <x-admin.filters.item label="جستجوی کاربر"><x-admin.form.input name="user_query" :value="request('user_query')" placeholder="نام، موبایل یا ایمیل"/></x-admin.filters.item>
                <x-admin.filters.item label="شناسه سری صدور"><x-admin.form.input name="grant_id" type="number" :value="request('grant_id')"/></x-admin.filters.item>
                <x-admin.filters.item label="زمان صدور"><div class="flex gap-2"><x-admin.form.date-picker name="issued_at_from" :value="request('issued_at_from')" placeholder="از"/><x-admin.form.date-picker name="issued_at_to" :value="request('issued_at_to')" placeholder="تا"/></div></x-admin.filters.item>
            </x-admin.filters.grid>
            <x-admin.filters.actions><x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>@if($hasActiveFilters)<x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.tickets.index',$drawCampaign) }}" variant="secondary">حذف فیلتر</x-admin.form.button>@endif</x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
