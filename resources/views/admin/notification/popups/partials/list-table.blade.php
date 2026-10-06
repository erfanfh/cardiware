<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            @if($hasBulkActions)
                <x-admin.list.th width="44px" align="center">
                    <x-admin.list.bulk-checkbox
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($popupIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($popupIds) . ')'"
                    />
                </x-admin.list.th>
            @endif
            <x-admin.list.th width="300px" label="عنوان" sortable sortKey="title" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="180px">محل نمایش</x-admin.list.th>
            <x-admin.list.th width="170px">مخاطب</x-admin.list.th>
            <x-admin.list.th width="160px">زمان‌بندی</x-admin.list.th>
            <x-admin.list.th width="120px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="145px" label="زمان ایجاد" sortable sortKey="created_at" :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($popups as $popup)
            <x-admin.list.tr ::class="{'bg-slate-50/90': isSelected({{ $popup->id }})}">
                @if($hasBulkActions)
                    <x-admin.list.td align="center">
                        <x-admin.list.bulk-checkbox
                            name="ids[]"
                            :value="$popup->id"
                            x-bind:checked="isSelected({{ $popup->id }})"
                            x-on:change="toggleSelection({{ $popup->id }})"
                        />
                    </x-admin.list.td>
                @endif
                <x-admin.list.td>
                    <div class="flex min-w-[240px] items-center gap-3">
                        @if($popup->image?->url)
                            <img src="{{ $popup->image->url }}" alt="" class="h-12 w-12 shrink-0 rounded-xl object-cover ring-1 ring-slate-200" loading="lazy">
                        @else
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 ring-1 ring-orange-100">
                                <span class="text-xl font-black">!</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            @can('popup.edit')
                                <a href="{{ route('admin.popups.edit', $popup) }}" class="block truncate font-bold text-slate-800 transition hover:text-brand-600" title="{{ $popup->title }}">{{ $popup->title }}</a>
                            @else
                                <span class="block truncate font-bold text-slate-800" title="{{ $popup->title }}">{{ $popup->title }}</span>
                            @endcan
                            <span class="text-xs text-slate-400">#{{ $popup->id }}</span>
                            <x-admin.list.row-actions>
                                @can('popup.edit')
                                    <x-admin.list.action tag="a" href="{{ route('admin.popups.edit', $popup) }}" variant="primary">ویرایش</x-admin.list.action>
                                @endcan
                                @if(\Illuminate\Support\Facades\Gate::allows('popup.edit') && \Illuminate\Support\Facades\Gate::allows('popup.delete'))
                                    <span class="text-slate-300">|</span>
                                @endif
                                @can('popup.delete')
                                    <x-admin.form method="delete" action="{{ route('admin.popups.destroy', $popup) }}" data-confirm-title="حذف پاپ‌آپ" data-confirm-text="پاپ‌آپ «{{ $popup->title }}» حذف شود؟">
                                        <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                                    </x-admin.form>
                                @endcan
                            </x-admin.list.row-actions>
                        </div>
                    </div>
                </x-admin.list.td>
                <x-admin.list.td>
                    <span class="font-semibold">{{ ['all' => 'همه صفحات', 'home' => 'صفحه اصلی', 'post' => 'نوشته‌ها', 'page' => 'برگه‌ها', 'product' => 'محصولات'][$popup->target_type] ?? $popup->target_type }}</span>
                    @if($popup->target_id)<span class="block text-xs text-slate-500">فقط مورد #{{ $popup->target_id }}</span>@endif
                </x-admin.list.td>
                <x-admin.list.td>{{ ['all' => 'همه کاربران', 'guest' => 'مهمانان', 'authenticated' => 'واردشده‌ها', 'vip' => 'VIP فعال'][$popup->audience] ?? $popup->audience }}</x-admin.list.td>
                <x-admin.list.td>
                    <span class="font-semibold">{{ number_format($popup->delay_seconds) }} ثانیه تأخیر</span>
                    <span class="block text-xs text-slate-500">بستن: {{ number_format($popup->close_minutes) }} دقیقه</span>
                </x-admin.list.td>
                <x-admin.list.td>
                    <x-admin.badge :color="$popup->is_active ? 'green' : 'slate'">{{ $popup->is_active ? 'فعال' : 'غیرفعال' }}</x-admin.badge>
                </x-admin.list.td>
                <x-admin.list.td muted>{{ jalali_date($popup->created_at, 'Y/m/d H:i') }}</x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                :colspan="$hasBulkActions ? 7 : 6"
                title="پاپ‌آپی پیدا نشد"
                description="فیلترها را تغییر دهید یا یک پاپ‌آپ جدید بسازید."
                :action-text="\Illuminate\Support\Facades\Gate::allows('popup.create') ? 'ایجاد پاپ‌آپ' : null"
                :action-href="\Illuminate\Support\Facades\Gate::allows('popup.create') ? route('admin.popups.create') : null"
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
