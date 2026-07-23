<div class="space-y-4">
    <x-admin.list.table>
        <x-admin.list.thead>
            <tr>
                <x-admin.list.th
                    width="44px"
                    align="center"
                >
                    <x-admin.list.bulk-checkbox
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($teamMemberIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($teamMemberIds) . ')'"
                    />
                </x-admin.list.th>

                <x-admin.list.th width="90px">
                    تصویر
                </x-admin.list.th>

                <x-admin.list.th
                    label="نام کامل"
                    sortable
                    sortKey="first_name"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
                />

                <x-admin.list.th>
                    عنوان شغلی
                </x-admin.list.th>

                <x-admin.list.th>
                    موبایل
                </x-admin.list.th>

                <x-admin.list.th>
                    ایمیل
                </x-admin.list.th>

                <x-admin.list.th>
                    وب‌سایت
                </x-admin.list.th>

                <x-admin.list.th
                    width="130px"
                    label="تاریخ ایجاد"
                    sortable
                    sortKey="created_at"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
                />
            </tr>
        </x-admin.list.thead>

        <x-admin.list.tbody>
            @if($teamMembers->isNotEmpty())
                @foreach($teamMembers as $teamMember)
                    <x-admin.list.tr
                        ::class="{
                            'bg-brand-50/50 ring-1 ring-inset ring-brand-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)]': isOpen({{ $teamMember->id }}),
                            'bg-slate-50/90': !isOpen({{ $teamMember->id }}) && isSelected({{ $teamMember->id }})
                        }"
                    >
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox
                                name="ids[]"
                                :value="$teamMember->id"
                                x-bind:checked="isSelected({{ $teamMember->id }})"
                                x-on:change="toggleSelection({{ $teamMember->id }})"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.list.thumbnail
                                :src="$teamMember->picture?->url"
                                :alt="$teamMember->full_name"
                                size="md-square"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div>
                                <p
                                    class="line-clamp-1 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                                >
                                    {{ $teamMember->full_name }}
                                </p>

                                <div
                                    class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500"
                                >
                                    <span>#{{ $teamMember->id }}</span>
                                </div>

                                <x-admin.list.row-actions>
                                    <x-admin.list.action
                                        tag="a"
                                        href="{{ route('admin.team-members.edit', $teamMember) }}"
                                        variant="primary"
                                    >
                                        ویرایش
                                    </x-admin.list.action>

                                    <span class="text-slate-300">|</span>

                                    <x-admin.form
                                        action="{{ route('admin.team-members.destroy', $teamMember) }}"
                                        method="delete"
                                        class="js-confirm"
                                        data-confirm-title="حذف عضو تیم"
                                        data-confirm-text="آیا از حذف عضو تیم «{{ $teamMember->full_name }}» مطمئن هستید؟"
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
                            <div class="text-xs leading-6 text-slate-700">
                                {{ $teamMember->job_title ?: '—' }}
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div
                                dir="ltr"
                                class="text-left text-xs leading-6 text-slate-700"
                            >
                                {{ $teamMember->mobile ?: '—' }}
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div
                                dir="ltr"
                                class="max-w-[220px] truncate text-left text-xs leading-6 text-slate-700"
                            >
                                @if($teamMember->email)
                                    <a
                                        href="mailto:{{ $teamMember->email }}"
                                        class="transition hover:text-brand-600"
                                    >
                                        {{ $teamMember->email }}
                                    </a>
                                @else
                                    —
                                @endif
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div
                                dir="ltr"
                                class="max-w-[200px] truncate text-left text-xs leading-6"
                            >
                                @if($teamMember->website_url)
                                    <a
                                        href="{{ $teamMember->website_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-brand-600 transition hover:text-brand-700"
                                    >
                                        {{ $teamMember->website_url }}
                                    </a>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>
                                    {{ jalali_date($teamMember->created_at, 'Y/m/d') }}
                                </div>

                                <div>
                                    {{ jalali_date($teamMember->created_at, 'H:i') }}
                                </div>
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>
                @endforeach
            @else
                <x-admin.list.empty
                    colspan="8"
                    title="عضوی برای نمایش وجود ندارد"
                    description="هنوز هیچ عضو تیمی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                    action-text="افزودن عضو جدید"
                    :action-href="route('admin.team-members.create')"
                />
            @endif
        </x-admin.list.tbody>
    </x-admin.list.table>
</div>
