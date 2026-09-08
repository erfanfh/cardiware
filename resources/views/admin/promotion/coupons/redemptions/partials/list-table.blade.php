<x-admin.list.table>
    <x-admin.list.thead>
        <tr>
            <x-admin.list.th width="90px">شناسه</x-admin.list.th>
            <x-admin.list.th width="280px">کاربر</x-admin.list.th>
            <x-admin.list.th width="170px">سفارش</x-admin.list.th>
            <x-admin.list.th
                width="170px"
                label="مبلغ سفارش"
                sortable
                sortKey="order_subtotal"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
            <x-admin.list.th width="170px">مبلغ مشمول</x-admin.list.th>
            <x-admin.list.th
                width="180px"
                label="تخفیف اعمال‌شده"
                sortable
                sortKey="discount_amount"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
            <x-admin.list.th
                width="160px"
                label="زمان استفاده"
                sortable
                sortKey="redeemed_at"
                :current-sort="request('sort')"
                :current-direction="request('direction', 'desc')"
            />
        </tr>
    </x-admin.list.thead>

    <x-admin.list.tbody>
        @forelse($redemptions as $redemption)
            @php
                $user = $redemption->user;
                $order = $redemption->order;
                $userName = $user
                    ? ($user->display_name ?: trim($user->first_name . ' ' . $user->last_name))
                    : null;
                $redeemedAt = $redemption->redeemed_at ?: $redemption->created_at;
            @endphp

            <x-admin.list.tr>
                <x-admin.list.td muted>
                    <span class="text-xs font-bold">#{{ $redemption->id }}</span>
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($user)
                        <div class="min-w-[230px]">
                            <div class="flex items-center gap-3">
                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-sm font-black text-brand-700">
                                    {{ mb_substr($userName ?: 'ک', 0, 1) }}
                                </div>

                                <div class="min-w-0">
                                    @if(!$user->trashed() && auth()->user()?->can('user.edit'))
                                        <a
                                            href="{{ route('admin.users.edit', $user) }}"
                                            class="block truncate text-sm font-extrabold text-slate-800 transition hover:text-brand-600"
                                        >
                                            {{ $userName ?: 'کاربر بدون نام' }}
                                        </a>
                                    @else
                                        <div class="truncate text-sm font-extrabold text-slate-800">
                                            {{ $userName ?: 'کاربر بدون نام' }}
                                        </div>
                                    @endif

                                    <div class="mt-1 flex flex-wrap gap-x-2 text-[11px] text-slate-500">
                                        <span>#{{ $user->id }}</span>
                                        @if($user->mobile)
                                            <span dir="ltr">{{ $user->mobile }}</span>
                                        @endif
                                        @if($user->trashed())
                                            <span class="font-bold text-rose-500">حذف‌شده</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($user->email)
                                <div class="mt-2 truncate text-[11px] text-slate-400" dir="ltr">
                                    {{ $user->email }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-xs italic leading-6 text-slate-500">
                            کاربر حذف شده یا اطلاعات او در دسترس نیست.
                        </div>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    @if($order)
                        @can('order.edit')
                            <a
                                href="{{ route('admin.orders.edit', $order) }}"
                                class="whitespace-nowrap text-sm font-extrabold text-brand-700 transition hover:text-brand-800"
                                dir="ltr"
                            >
                                {{ $order->order_number }}
                            </a>
                        @else
                            <span class="whitespace-nowrap text-sm font-bold text-slate-700" dir="ltr">
                                {{ $order->order_number }}
                            </span>
                        @endcan
                    @else
                        <span class="text-xs text-slate-400">سفارش در دسترس نیست</span>
                    @endif
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="whitespace-nowrap text-sm font-bold text-slate-800">
                        {{ number_format((int) $redemption->order_subtotal) }}
                        <span class="text-[11px] font-normal text-slate-500">تومان</span>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="whitespace-nowrap text-sm font-bold text-slate-700">
                        {{ number_format((int) $redemption->eligible_amount) }}
                        <span class="text-[11px] font-normal text-slate-500">تومان</span>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td>
                    <div class="whitespace-nowrap text-sm font-black text-emerald-700">
                        {{ number_format((int) $redemption->discount_amount) }}
                        <span class="text-[11px] font-normal text-emerald-600">تومان</span>
                    </div>
                </x-admin.list.td>

                <x-admin.list.td muted>
                    @if($redeemedAt)
                        <div class="whitespace-nowrap text-xs font-bold text-slate-600">
                            {{ jalali_date($redeemedAt, 'Y/m/d') }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-400">
                            ساعت {{ jalali_date($redeemedAt, 'H:i') }}
                        </div>
                    @else
                        <span class="text-xs text-slate-400">ثبت نشده</span>
                    @endif
                </x-admin.list.td>
            </x-admin.list.tr>
        @empty
            <x-admin.list.empty
                title="استفاده‌ای برای این کدتخفیف پیدا نشد"
                description="هنوز خرید موفقی با این کد ثبت نشده یا فیلترهای فعلی نتیجه‌ای ندارند."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>
