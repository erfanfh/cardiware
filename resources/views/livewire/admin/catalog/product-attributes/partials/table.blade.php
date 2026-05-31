<x-admin.list.table>
    <x-admin.list.thead>
        <tr class="text-right">
            <th class="w-14 px-4 py-3">
                <x-admin.list.bulk-checkbox
                    x-bind:checked="allVisibleSelected"
                    x-on:change="toggleVisible()"
                />
            </th>

            <x-admin.list.th
                width="220px"
                label="عنوان"
                sortable
                sortKey="title"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="مقدارها"
                sortable
                sortKey="values_count"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="استفاده"
                sortable
                sortKey="assignments_count"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="تاریخ"
                sortable
                sortKey="created_at"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($items as $item)
            @include('livewire.admin.catalog.product-attributes.partials.table-row', [
                'item' => $item,
                'highlightedId' => $highlightedId,
                'editingId' => $editingId,
            ])
        @empty
            <x-admin.list.empty
                title="ویژگی محصولی برای نمایش وجود ندارد"
                description="هنوز هیچ ویژگی محصولی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>

@if($items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endif
