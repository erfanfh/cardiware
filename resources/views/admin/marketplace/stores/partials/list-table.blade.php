<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="44px" align="center">
                <x-admin.list.bulk-checkbox
                    :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($storeIds) . ')'"
                    :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($storeIds) . ')'"
                />
            </x-admin.list.th>

            <x-admin.list.th width="90px">تصویر</x-admin.list.th>

            <x-admin.list.th>عنوان فروشنده</x-admin.list.th>

            <x-admin.list.th width="140px">وضعیت</x-admin.list.th>

            <x-admin.list.th width="130px">محصولات</x-admin.list.th>

            <x-admin.list.th width="130px">کاربران</x-admin.list.th>

            <x-admin.list.th>بازدید‌ها</x-admin.list.th>

            <x-admin.list.th>امتیاز سئو</x-admin.list.th>

            <x-admin.list.th
                width="130px"
                label="زمان ایجاد"
                sortable
                sortKey="created_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @if($stores->isNotEmpty())
            @foreach($stores as $store)
                <x-admin.list.tr
                    ::class="{
                        'bg-slate-50/90': isSelected({{ $store->id }})
                    }"
                >
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox
                            name="ids[]"
                            :value="$store->id"
                            x-bind:checked="isSelected({{ $store->id }})"
                            x-on:change="toggleSelection({{ $store->id }})"
                        />
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.list.thumbnail
                            :src="$store->picture?->url"
                            :alt="$store->title"
                            size="md"
                        />
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <div class="min-w-[280px]">
                            <a
                                href="{{ filled($store->slug) ? route('stores.detail', $store->slug) : '#' }}"
                                class="inline-block leading-6 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                            >
                                {{ $store->title }}
                            </a>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span>#{{ $store->id }}</span>

                                @if($store->slug)
                                    <span class="text-slate-300 pb-1">•</span>
                                    <span dir="ltr">{{ $store->slug }}</span>
                                @endif
                            </div>

                            <x-admin.list.row-actions>
                                <x-admin.list.action
                                    tag="a"
                                    href="{{ route('admin.stores.edit', $store) }}"
                                    variant="primary"
                                >
                                    ویرایش
                                </x-admin.list.action>

                                @can('analytics.view')
                                    <span class="text-slate-300">|</span>

                                    <x-admin.list.action
                                        tag="a"
                                        href="{{ route('admin.stores.analytics', $store) }}"
                                    >
                                        گزارش فروش
                                    </x-admin.list.action>
                                @endcan

                                <span class="text-slate-300">|</span>

                                <x-admin.form
                                    action="{{ route('admin.stores.destroy', $store) }}"
                                    method="delete"
                                    class="js-confirm"
                                    data-confirm-title="حذف فروشنده"
                                    data-confirm-text="آیا از حذف فروشنده «{{ $store->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
                                >
                                    <x-admin.list.action
                                        tag="button"
                                        type="submit"
                                        variant="danger"
                                    >
                                        حذف
                                    </x-admin.list.action>
                                </x-admin.form>
                            </x-admin.list.row-actions>
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        @php
                            $statusColor = match($store->status) {
                                \App\Domain\Marketplace\Enums\StoreStatusEnum::ACTIVE => 'green',
                                \App\Domain\Marketplace\Enums\StoreStatusEnum::INACTIVE => 'red',
                                default => 'slate',
                            };
                        @endphp

                        <x-admin.badge
                            :color="$statusColor"
                            size="md"
                        >
                            {{ $store->status?->label() ?? 'نامشخص' }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <a href="{{ route('admin.products.index') . '?store_id=' . $store->id }}">
                            <x-admin.badge color="blue" size="md">
                                {{ $store->products_count }}
                            </x-admin.badge>
                        </a>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge color="slate" size="md">
                            {{ $store->users_count }}
                        </x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.badge size="md">{{ page_views($store) }}</x-admin.badge>
                    </x-admin.list.td>

                    <x-admin.list.td>
                        <x-admin.seo.score-indicator :score="$store->seoAnalysis?->score"/>
                    </x-admin.list.td>

                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($store->created_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($store->created_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @endforeach
        @else
            <x-admin.list.empty
                title="فروشنده‌ای برای نمایش وجود ندارد"
                description="هنوز هیچ فروشنده‌ای ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                action-text="افزودن فروشنده جدید"
                :action-href="route('admin.stores.create')"
            />
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
