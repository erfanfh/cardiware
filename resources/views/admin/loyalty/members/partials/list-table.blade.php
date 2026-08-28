<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="300px">کاربر</x-admin.list.th>
            <x-admin.list.th width="150px">نقش و وضعیت</x-admin.list.th>

            <x-admin.list.th
                width="145px"
                label="موجودی فعلی"
                sortable
                sortKey="balance"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />

            <x-admin.list.th
                width="145px"
                label="کسب شده"
                sortable
                sortKey="lifetime_earned"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />

            <x-admin.list.th
                width="145px"
                label="مصرف شده"
                sortable
                sortKey="lifetime_spent"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />

            <x-admin.list.th width="135px">وضعیت باشگاه</x-admin.list.th>

            <x-admin.list.th
                width="135px"
                label="تاریخ عضویت"
                sortable
                sortKey="created_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($members as $member)
            @php
                $balance = (int)($member->points_balance ?? 0);
                $lifetimeEarned = (int)($member->points_lifetime_earned ?? 0);
                $lifetimeSpent = (int)($member->points_lifetime_spent ?? 0);
                $hasAccount = (bool)$member->pointAccount;
            @endphp

            <x-admin.list.tr>
                <x-admin.list.td>
                    <div class="flex min-w-[250px] items-center gap-3">
                        <x-admin.list.thumbnail
                            :src="$member->avatar?->url"
                            :alt="$member->full_name"
                            size="sm-square"
                        />

                        <div class="min-w-0">
                            @can('loyalty-account.show')
                                <a
                                    href="{{ route('admin.loyalty-members.show', $member) }}"
                                    class="block truncate text-sm font-extrabold text-slate-800 transition hover:text-brand-600"
                                >
                                    {{ $member->full_name }}
                                </a>
                            @else
                                <div class="truncate text-sm font-extrabold text-slate-800">{{ $member->full_name }}</div>
                            @endcan

                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-slate-500">
                                <span>#{{ $member->id }}</span>
                                @if($member->username)
                                    <span dir="ltr">{{ '@' . $member->username }}</span>
                                @endif
                                @if($member->mobile)
                                    <span>{{ $member->mobile }}</span>
                                @endif
                            </div>

                            <x-admin.list.row-actions>
                                @can('loyalty-account.show')
                                    <x-admin.list.action
                                        tag="a"
                                        href="{{ route('admin.loyalty-members.show', $member) }}"
                                        variant="primary"
                                    >
                                        جزئیات امتیاز
                                    </x-admin.list.action>
                                @endcan

                                @can('user.edit')
                                    @can('loyalty-account.show')<span class="text-slate-300">|</span>@endcan
                                    <x-admin.list.action
                                        tag="a"
                                        href="{{ route('admin.users.edit', $member) }}"
                                    >
                                        ویرایش کاربر
                                    </x-admin.list.action>
                                @endcan
                            </x-admin.list.row-actions>
                        </div>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="space-y-2">
                        <x-admin.badge color="slate" size="md">{{ $member->role?->title ?? 'بدون نقش' }}</x-admin.badge>

                        @if($member->status === \App\Domain\Identity\Enums\UserStatusEnum::ACTIVE)
                            <x-admin.badge color="green" size="sm">فعال</x-admin.badge>
                        @else
                            <x-admin.badge color="red" size="sm">غیرفعال</x-admin.badge>
                        @endif
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-sm font-black {{ $balance < 0 ? 'text-red-600' : 'text-slate-900' }}">
                        {{ number_format($balance) }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-sm font-black text-emerald-700">{{ number_format($lifetimeEarned) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="text-sm font-black text-slate-700">{{ number_format($lifetimeSpent) }}</div>
                    <div class="mt-1 text-[11px] text-slate-400">امتیاز</div>
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($hasAccount)
                        <x-admin.badge color="green" size="md">فعال شده</x-admin.badge>
                    @else
                        <x-admin.badge color="slate" size="md">بدون تراکنش</x-admin.badge>
                    @endif

                    @if($balance < 0)
                        <div class="mt-2">
                            <x-admin.badge color="red" size="sm">بدهی امتیاز</x-admin.badge>
                        </div>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td muted>
                    <div class="text-xs leading-6">
                        <div>{{ jalali_date($member->created_at, 'Y/m/d') }}</div>
                        <div>{{ jalali_date($member->created_at, 'H:i') }}</div>
                    </div>
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                title="کاربری برای نمایش وجود ندارد"
                description="نتیجه‌ای با فیلترهای فعلی باشگاه مخاطبان پیدا نشد."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
