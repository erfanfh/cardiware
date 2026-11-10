<div class="space-y-4">
    <x-admin.list.table>
        <x-admin.list.thead>
            <tr>
                <x-admin.list.th width="44px" align="center">
                    <x-admin.list.bulk-checkbox
                        :x-bind:checked="'isAllSelected(' . \Illuminate\Support\Js::from($userIds) . ')'"
                        :x-on:change="'toggleAll(' . \Illuminate\Support\Js::from($userIds) . ')'"
                    />
                </x-admin.list.th>

                <x-admin.list.th width="90px">تصویر</x-admin.list.th>

                <x-admin.list.th class="min-w-40">نام کامل</x-admin.list.th>

                <x-admin.list.th class="min-w-40">نام نمایشی</x-admin.list.th>

                <x-admin.list.th>نام کاربری</x-admin.list.th>

                <x-admin.list.th>موبایل</x-admin.list.th>

                <x-admin.list.th>ایمیل</x-admin.list.th>

                <x-admin.list.th>کیف پول</x-admin.list.th>

                <x-admin.list.th>وضعیت</x-admin.list.th>

                <x-admin.list.th width="150px">اشتراک VIP</x-admin.list.th>

                <x-admin.list.th>نقش</x-admin.list.th>

                <x-admin.list.th>نوشته‌ها</x-admin.list.th>

                <x-admin.list.th
                    width="130px"
                    label="تاریخ ثبت نام"
                    sortable
                    sortKey="created_at"
                    :current-sort="request('sort')"
                    :current-direction="request('direction', 'desc')"
                />
            </tr>
        </x-admin.list.thead>

        <x-admin.list.tbody>
            @if($users->isNotEmpty())
                @foreach($users as $user)
                    <x-admin.list.tr
                        ::class="{
                                    'bg-brand-50/50 ring-1 ring-inset ring-brand-100 shadow-[inset_0_1px_0_rgba(255,255,255,0.6)]': isOpen({{ $user->id }}),
                                    'bg-slate-50/90': !isOpen({{ $user->id }}) && isSelected({{ $user->id }})
                                }"
                    >
                        <x-admin.list.td align="center">
                            <x-admin.list.bulk-checkbox
                                name="ids[]"
                                :value="$user->id"
                                x-bind:checked="isSelected({{ $user->id }})"
                                x-on:change="toggleSelection({{ $user->id }})"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <x-admin.list.thumbnail
                                :src="$user->profileAvatarUrl()"
                                :alt="$user->title"
                                size="md-square"
                            />
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div>
                                <p class="line-clamp-1 text-sm font-bold text-slate-800 transition hover:text-brand-600"
                                >{{ $user->full_name }}</p>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span>#{{ $user->id }}</span>
                                </div>

                                <x-admin.list.row-actions>
                                    <x-admin.list.action
                                        tag="a"
                                        href="{{ route('admin.users.edit', $user) }}"
                                        variant="primary"
                                    >
                                        ویرایش
                                    </x-admin.list.action>

                                    @can('wallet.adjust')
                                        <span class="text-slate-300">|</span>
                                        <x-admin.list.action tag="a" href="{{ route('admin.users.wallet.show', $user) }}" variant="primary">
                                            کیف پول
                                        </x-admin.list.action>
                                    @endcan

                                    <span class="text-slate-300">|</span>

                                    <x-admin.form
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        method="delete"
                                        class="js-confirm"
                                        data-confirm-title="حذف کاربر"
                                        data-confirm-text="آیا از حذف کاربر «{{ $user->full_name }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست."
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
                            <div class="text-xs leading-6">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $user->display_name }}</span>
                                    @if(($user->club_bio_status ?? null) === 'pending')
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="rounded-lg bg-amber-100 px-1.5 py-0.5 text-[10px] font-black text-amber-800 transition hover:bg-amber-200"
                                           title="معرفی باشگاه این کاربر در انتظار تایید است — برای بررسی کلیک کنید">
                                            بیو در انتظار تایید
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="text-xs leading-6">
                                <div>{{ $user->username }}</div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="text-xs leading-6">
                                <div>{{ $user->mobile }}</div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="text-xs leading-6">
                                <div>{{ $user->email }}</div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="text-xs leading-6">
                                <div class="whitespace-nowrap">
                                    {{ $user->wallet?->balance ? number_format($user->wallet?->balance) . ' تومان' : 'بدون موجودی!' }}
                                </div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            @if($user->status == \App\Domain\Identity\Enums\UserStatusEnum::ACTIVE)
                                <x-admin.badge
                                    color="green"
                                >فعال
                                </x-admin.badge>
                            @endif
                            @if($user->status == \App\Domain\Identity\Enums\UserStatusEnum::INACTIVE)
                                <x-admin.badge
                                    color="red"
                                >غیرفعال
                                </x-admin.badge>
                            @endif
                        </x-admin.list.td>

                        <x-admin.list.td>
                            @if($user->has_active_vip && $user->vip_expires_at)
                                @php
                                    $vipExpiresAt = \Illuminate\Support\Carbon::parse($user->vip_expires_at);
                                    $vipDaysRemaining = max(1, (int) ceil(($vipExpiresAt->getTimestamp() - $vipNow->getTimestamp()) / 86400));
                                @endphp
                                <div class="flex flex-col items-start gap-1.5 whitespace-nowrap"
                                     title="پایان اعتبار: {{ jalali_date($vipExpiresAt, 'j F Y H:i') }}">
                                    <x-admin.badge color="brand">VIP فعال</x-admin.badge>
                                    <span class="text-xs font-semibold text-slate-600">
                                        {{ number_format($vipDaysRemaining) }} روز باقی‌مانده
                                    </span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="text-xs leading-6">
                                <div class="whitespace-nowrap">{{ $user->role->title }}</div>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td>
                            <div class="text-xs leading-6">
                                <a href="{{ route('admin.posts.index', ['author_ids' => [$user->id]]) }}">
                                    <x-admin.badge
                                        color="gray"
                                    >{{ $user->posts_count }}</x-admin.badge>
                                </a>
                            </div>
                        </x-admin.list.td>

                        <x-admin.list.td muted>
                            <div class="text-xs leading-6">
                                <div>{{ jalali_date($user->created_at, 'Y/m/d') }}</div>
                                <div>{{ jalali_date($user->created_at, 'H:i') }}</div>
                            </div>
                        </x-admin.list.td>
                    </x-admin.list.tr>

                @endforeach
            @else
                <x-admin.list.empty
                    colspan="13"
                    title="کاربر‌ی برای نمایش وجود ندارد"
                    description="هنوز هیچ کاربر‌ی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                    action-text="افزودن کاربر جدید"
                    :action-href="route('admin.users.create')"
                />
            @endif
        </x-admin.list.tbody>
    </x-admin.list.table>
</div>
