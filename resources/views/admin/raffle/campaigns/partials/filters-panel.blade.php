<x-admin.form method="get" action="{{ route('admin.draw-campaigns.index') }}">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی قرعه کشی ها">
            <x-slot:actions>
                @can('raffle-campaign.create')
                    <x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.create') }}" variant="solid">+ ایجاد قرعه کشی</x-admin.form.button>
                @endcan
            </x-slot:actions>
        </x-admin.filters.header>
        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه"><x-admin.form.input name="id" :value="request('id')"/></x-admin.filters.item>
                <x-admin.filters.item label="عنوان"><x-admin.form.input name="title" :value="request('title')"/></x-admin.filters.item>
                <x-admin.filters.item label="اسلاگ"><x-admin.form.input name="slug" dir="ltr" :value="request('slug')"/></x-admin.filters.item>
                <x-admin.filters.item label="وضعیت"><x-admin.form.select name="status" :options="['' => 'همه وضعیت ها'] + $statuses" :value="request('status')"/></x-admin.filters.item>
                <x-admin.filters.item label="زمان شروع"><div class="flex gap-2"><x-admin.form.date-picker name="starts_at_from" :value="request('starts_at_from')" placeholder="از"/><x-admin.form.date-picker name="starts_at_to" :value="request('starts_at_to')" placeholder="تا"/></div></x-admin.filters.item>
                <x-admin.filters.item label="زمان پایان"><div class="flex gap-2"><x-admin.form.date-picker name="closes_at_from" :value="request('closes_at_from')" placeholder="از"/><x-admin.form.date-picker name="closes_at_to" :value="request('closes_at_to')" placeholder="تا"/></div></x-admin.filters.item>
            </x-admin.filters.grid>
            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                @if($hasActiveFilters)<x-admin.form.button tag="a" href="{{ route('admin.draw-campaigns.index') }}" variant="secondary">حذف فیلترها</x-admin.form.button>@endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
