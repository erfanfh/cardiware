<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            @if($hasBulkActions)
                <x-admin.list.th width="44px" align="center">
                    <x-admin.list.bulk-checkbox
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($planIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($planIds) . ')'"
                    />
                </x-admin.list.th>
            @endif

            <x-admin.list.th width="290px">پلن</x-admin.list.th>
            <x-admin.list.th width="130px" label="مدت اعتبار" sortable sortKey="duration_days"
                             :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="160px" label="قیمت" sortable sortKey="price_toman"
                             :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="130px">وضعیت</x-admin.list.th>
            <x-admin.list.th width="100px" label="ترتیب" sortable sortKey="sort_order"
                             :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
            <x-admin.list.th width="150px" label="زمان ایجاد" sortable sortKey="created_at"
                             :current-sort="request('sort')" :current-direction="request('direction', 'desc')"/>
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @if($plans->isNotEmpty())
            @foreach($plans as $plan)
                <x-admin.list.tr ::class="{'bg-slate-50/90': isSelected({{ $plan->id }})}">
                    @if($hasBulkActions)
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox name="ids[]" :value="$plan->id"
                                                        x-bind:checked="isSelected({{ $plan->id }})"
                                                        x-on:change="toggleSelection({{ $plan->id }})"/>
                        </x-admin.list.td>
                    @endif

                    <x-admin.list.td>
                        <div class="min-w-[220px] max-w-[290px]">
                            @can('vip-plan.edit')
                                <a href="{{ route('admin.vip-plans.edit', $plan) }}"
                                   class="block truncate text-sm font-bold leading-6 text-slate-800 transition hover:text-brand-600"
                                   title="{{ $plan->title }}">{{ $plan->title }}</a>
                            @else
                                <span class="block truncate text-sm font-bold leading-6 text-slate-800" title="{{ $plan->title }}">{{ $plan->title }}</span>
                            @endcan

                            <div class="mt-2 flex items-center gap-2">
                                <x-admin.badge color="dark" size="sm" class="max-w-[180px] truncate font-bold tracking-wider"
                                               dir="ltr" title="{{ $plan->code }}">{{ $plan->code }}</x-admin.badge>
                                <span class="text-[11px] text-slate-400">#{{ $plan->id }}</span>
                            </div>

                            @if(Gate::allows('vip-plan.edit') || Gate::allows('vip-plan.delete'))
                                <x-admin.list.row-actions>
                                    @can('vip-plan.edit')
                                        <x-admin.list.action tag="a" href="{{ route('admin.vip-plans.edit', $plan) }}" variant="primary">
                                            ویرایش
                                        </x-admin.list.action>
                                    @endcan

                                    @if(Gate::allows('vip-plan.edit') && Gate::allows('vip-plan.delete'))
                                        <span class="text-slate-300">|</span>
                                    @endif

                                    @can('vip-plan.delete')
                                        <x-admin.form action="{{ route('admin.vip-plans.destroy', $plan) }}" method="delete"
                                                      data-confirm-title="حذف پلن VIP"
                                                      data-confirm-text="آیا از حذف پلن «{{ $plan->title }}» مطمئن هستید؟">
                                            <x-admin.list.action tag="button" type="submit" variant="danger">حذف</x-admin.list.action>
                                        </x-admin.form>
                                    @endcan
                                </x-admin.list.row-actions>
                            @endif
                        </div>
                    </x-admin.list.td>

                    <x-admin.list.td>{{ number_format($plan->duration_days) }} روز</x-admin.list.td>
                    <x-admin.list.td>{{ number_format($plan->price_toman) }} تومان</x-admin.list.td>
                    <x-admin.list.td>
                        <x-admin.badge :color="$plan->is_active ? 'green' : 'slate'" size="md">
                            {{ $plan->is_active ? 'فعال' : 'غیرفعال' }}
                        </x-admin.badge>
                    </x-admin.list.td>
                    <x-admin.list.td>{{ $plan->sort_order }}</x-admin.list.td>
                    <x-admin.list.td muted>
                        <div class="text-xs leading-6">
                            <div>{{ jalali_date($plan->created_at, 'Y/m/d') }}</div>
                            <div>{{ jalali_date($plan->created_at, 'H:i') }}</div>
                        </div>
                    </x-admin.list.td>
                </x-admin.list.tr>
            @endforeach
        @else
            @can('vip-plan.create')
                <x-admin.list.empty title="پلن VIP برای نمایش وجود ندارد"
                                    description="هنوز پلنی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                                    action-text="افزودن پلن جدید" :action-href="route('admin.vip-plans.create')"/>
            @else
                <x-admin.list.empty title="پلن VIP برای نمایش وجود ندارد"
                                    description="نتیجه‌ای با فیلترهای فعلی پیدا نشده است."/>
            @endcan
        @endif
    </x-admin.list.tbody>
</x-admin.list.table>
