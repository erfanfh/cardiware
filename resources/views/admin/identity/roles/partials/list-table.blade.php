<div class="space-y-4">
    <x-admin.list.table>
        <x-admin.list.thead>
            <tr>
                <x-admin.list.th width="44px" align="center">
                    <x-admin.list.bulk-checkbox
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($roleIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($roleIds) . ')'"
                    />
                </x-admin.list.th>

                <x-admin.list.th>عنوان</x-admin.list.th>

                <x-admin.list.th>دسترسی‌ها</x-admin.list.th>

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
            @if($roles->isNotEmpty())
                @foreach($roles as $role)
                    <x-admin.list.tr
                        ::class="{
                                    'bg-brand-50/50 ring-1 ring-inset ring-brand-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)]': isOpen({{ $role->id }}),
                                    'bg-slate-50/90': !isOpen({{ $role->id }}) && isSelected({{ $role->id }})
                                }"
                    >
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox
                                name="ids[]"
                                :value="$role->id"
                                x-bind:checked="isSelected({{ $role->id }})"
                                x-on:change="toggleSelection({{ $role->id }})"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="min-w-[280px]">
                                <div class="flex gap-2">
                                    <p class="line-clamp-1 text-sm font-bold text-slate-800 transition">
                                        {{ $role->title }}
                                    </p>
                                    @if($role->is_default)
                                        <x-admin.badge color="blue">پیش فرض</x-admin.badge>
                                    @elseif($role->is_super_admin)
                                        <x-admin.badge color="red">ادمین کل</x-admin.badge>
                                    @endif
                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span>#{{ $role->id }}</span>
                                </div>

                                <x-admin.list.row-actions>
                                    <x-admin.list.action
                                        tag="a"
                                        :href="route('admin.roles.edit', $role)"
                                        variant="primary"
                                    >
                                        ویرایش
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.form
                                        :action="route('admin.roles.destroy', $role)"
                                        method="delete"
                                        class="js-confirm cursor-pointer"
                                        data-confirm-title="حذف نقش"
                                        data-confirm-text="آیا از حذف نقش «{{ $role->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
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
                            <div class="flex flex-wrap gap-2">
                                @forelse($role->permissions as $permission)
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200"
                                    >{{ $permission->label }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">بدون دسترسی</span>
                                @endforelse
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>{{ jalali_date($role->updated_at, 'Y/m/d') }}</div>
                                <div>{{ jalali_date($role->updated_at, 'H:i') }}</div>
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>
                @endforeach
            @else
                <x-admin.list.empty
                    colspan="4"
                    title="نقشی برای نمایش وجود ندارد"
                    description="هنوز هیچ نقشی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                    action-text="افزودن نقش جدید"
                    :action-href="route('admin.roles.create')"
                />
            @endif
        </x-admin.list.tbody>
    </x-admin.list.table>
</div>
