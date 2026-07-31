<div>
    <x-admin.page
        :columns="2"
        :sidebarSpan="4"
        sidebarPosition="right"
    >
        <x-slot:sidebar>
            @include('livewire.admin.catalog.product-medals.partials.form')
        </x-slot:sidebar>

        <div
            class="relative"
            x-data="{
                isUpdatingList: false,
                selectedIds: @entangle('selectedIds').live,
                visibleIds: @js(
                    $items->pluck('id')
                        ->map(fn ($id) => (string) $id)
                        ->values()
                        ->all()
                ),

                normalizeIds(ids) {
                    return (ids || []).map(id => String(id))
                },

                isSelected(id) {
                    return this.normalizeIds(this.selectedIds)
                        .includes(String(id))
                },

                toggleOne(id) {
                    id = String(id)

                    const ids = this.normalizeIds(this.selectedIds)

                    if (ids.includes(id)) {
                        this.selectedIds = ids.filter(
                            item => item !== id
                        )

                        return
                    }

                    this.selectedIds = [...ids, id]
                },

                get allVisibleSelected() {
                    const selected = this.normalizeIds(
                        this.selectedIds
                    )

                    return this.visibleIds.length > 0
                        && this.visibleIds.every(
                            id => selected.includes(String(id))
                        )
                },

                toggleVisible() {
                    const selected = this.normalizeIds(
                        this.selectedIds
                    )

                    if (this.allVisibleSelected) {
                        this.selectedIds = selected.filter(
                            id => !this.visibleIds.includes(String(id))
                        )

                        return
                    }

                    this.selectedIds = [
                        ...new Set([
                            ...selected,
                            ...this.visibleIds,
                        ]),
                    ]
                },

                clearSelections() {
                    this.selectedIds = []
                }
            }"
            x-ref="productMedalsListBox"
            x-on:product-medals-scroll-to-list.window="
                $refs.productMedalsListBox.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                })
            "
        >
            <div
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="save, gotoPage, previousPage, nextPage, search, performDeleteProductMedal, sortByColumn, applyBulkAction"
                class="transition duration-200"
            >
                <x-admin.metabox
                    :collapsible="false"
                    title="لیست مدال‌های محصولات"
                >
                    @include(
                        'livewire.admin.catalog.product-medals.partials.toolbar',
                        ['items' => $items]
                    )

                    @include(
                        'livewire.admin.catalog.product-medals.partials.bulk-bar'
                    )

                    @include(
                        'livewire.admin.catalog.product-medals.partials.table',
                        [
                            'items' => $items,
                            'sortBy' => $sortBy,
                            'sortDirection' => $sortDirection,
                            'highlightedId' => $highlightedId,
                            'editingId' => $editingId,
                        ]
                    )
                </x-admin.metabox>
            </div>

            @include(
                'livewire.admin.catalog.product-medals.partials.loading-overlay'
            )
        </div>
    </x-admin.page>
</div>
