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
                width="260px"
                label="عنوان"
                sortable
                sortKey="title"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="نیازمند محصول"
                sortable
                sortKey="requires_product"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="وضعیت"
                sortable
                sortKey="is_active"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="تیکت‌ها"
                sortable
                sortKey="tickets_count"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th
                label="ترتیب"
                sortable
                sortKey="sort_order"
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
            @include('livewire.admin.support.ticket-departments.partials.table-row', [
                'item' => $item,
                'highlightedId' => $highlightedId,
                'editingId' => $editingId,
            ])
        @empty
            <x-admin.list.empty
                title="دپارتمانی برای نمایش وجود ندارد"
                description="هنوز هیچ دپارتمان پشتیبانی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>

@if($items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endif
