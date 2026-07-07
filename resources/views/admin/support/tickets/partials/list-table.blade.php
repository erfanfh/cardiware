<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($ticketIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($ticketIds) . ')'"
                />
            </x-admin.list.th>

            <x-admin.list.th>کاربر</x-admin.list.th>
            <x-admin.list.th>تیکت</x-admin.list.th>
            <x-admin.list.th>دپارتمان / محصول</x-admin.list.th>

            <x-admin.list.th
                width="150px"
                label="آخرین پیام"
                sortable
                sortKey="last_message_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />

            <x-admin.list.th
                width="130px"
                label="تاریخ ثبت"
                sortable
                sortKey="created_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @if($tickets->isNotEmpty())
            @foreach($tickets as $ticket)
                @include('admin.support.tickets.partials.list-row', [
                    'ticket' => $ticket,
                ])
            @endforeach
        @else
            <x-admin.list.empty
                title="تیکتی برای نمایش وجود ندارد"
                description="هنوز هیچ تیکتی ثبت نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
