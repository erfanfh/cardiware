<x-admin.list.table>
    <x-admin.list.thead>
        <tr class="text-right">
            <th class="w-14 px-4 py-3">
                <label class="inline-flex cursor-pointer items-center justify-center">
                    <input
                        type="checkbox"
                        :checked="allVisibleSelected"
                        x-on:change="toggleVisible()"
                        class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                    >
                </label>
            </th>

            <x-admin.list.th
                width="170px"
                label="عنوان"
                sortable
                sortKey="title"
                :current-sort="$sortBy"
                :current-direction="$sortDirection"
                sortMode="livewire"
            />

            <x-admin.list.th>عبارت یکتا</x-admin.list.th>

            <x-admin.list.th>توضیحات</x-admin.list.th>

            <x-admin.list.th>استفاده</x-admin.list.th>

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
            @include('livewire.admin.taxonomy.partials.table-row', [
                'item' => $item,
                'taxonomyLabel' => $taxonomyLabel,
                'highlightedId' => $highlightedId,
                'editingId' => $editingId,
            ])
        @empty
            <x-admin.list.empty
                colspan="7"
                title="{{ $taxonomyLabel }}ی برای نمایش وجود ندارد"
                description="هنوز هیچ {{ $taxonomyLabel }}ی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>

@if($items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endif
